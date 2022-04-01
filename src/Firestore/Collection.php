<?php

namespace Tatter\Firebase\Firestore;

use BadMethodCallException;
use CodeIgniter\Validation\Validation;
use CodeIgniter\Validation\ValidationInterface;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FieldValue;
use Google\Cloud\Firestore\Query;
use InvalidArgumentException;
use Traversable;
use UnexpectedValueException;

/**
 * Collection Abstract Class
 *
 * Provides a model-like CRUD extension to Firestore's
 * CollectionReferenece class to add validation and
 * interpolate Entities wherever possible.
 *
 * @mixin CollectionReference
 */
abstract class Collection
{
    use ValidationTrait;

    /**
     * The name of the collection.
     */
    public const NAME = '';

    /**
     * The Entity type generate.
     *
     * @var class-string<Entity>
     */
    public const ENTITY = Entity::class;

    /**
     * Allowed CollectionReference methods
     * via __call(). All must return a Query.
     */
    private const QUERY_METHODS = [
        'endAt',
        'endBefore',
        'limit',
        'limitToLast',
        'offset',
        'orderBy',
        'select',
        'startAfter',
        'startAt',
        'where',
    ];

    /**
     * Firestore database connection
     */
    private CollectionReference $collection;

    /**
     * The table's primary key.
     */
    protected string $primaryKey = 'uid';

    /**
     * An array of field names that are allowed
     * to be set by the user in inserts/updates.
     */
    protected array $allowedFields = [];

    /**
     * If true, will set createdField, and updatedField
     * values on created Entities.
     */
    protected bool $useTimestamps = true;

    /**
     * The column used for insert timestamps.
     */
    protected ?string $createdField = 'createdAt';

    /**
     * The column used for update timestamps.
     */
    protected ?string $updatedField = 'updatedAt';

    final public function __construct(?CollectionReference $collection = null, ?ValidationInterface $validation = null)
    {
        $this->collection = $collection ?? service('firebase')->firestore->database()->collection(static::NAME);

        $this->setValidation($validation ?? service('validation', null, false));
        $this->initialize();
    }

    /**
     * Initializes the instance with any additional steps.
     * Optionally implemented by child classes.
     */
    protected function initialize()
    {
    }

    /**
     * Creates a new Entity with faked data.
     * Should be overridden by child classes
     * to make use of Faker fields.
     *
     * @param array<string, mixed> $overrides
     */
    public function fake(array $overrides = []): Entity
    {
        $class = static::ENTITY;

        return new $class($overrides);
    }

    /**
     * Returns the stored CollectionReference.
     */
    final public function collection(): CollectionReference
    {
        return $this->collection;
    }

    /**
     * Returns a DocumentReference to the given ID within
     * the underlying collection (need not exist).
     */
    final public function document(string $id): DocumentReference
    {
        return $this->collection->document($id);
    }

    /**
     * Returns the parent DocumentReference for a subcollection,
     * or null if root.
     */
    final public function parent(): ?DocumentReference
    {
        return $this->collection->parent();
    }

    /**
     * Returns the id of the underlying CollectionReference.
     */
    final public function id(): string
    {
        return $this->collection->id();
    }

    /**
     * Returns the name of the underlying CollectionReference.
     */
    final public function name(): string
    {
        return $this->collection->name();
    }

    /**
     * Returns the path of the underlying CollectionReference.
     */
    final public function path(): string
    {
        return $this->collection->path();
    }

    /**
     * Adds a new document from an Entity.
     *
     * @param array|Entity $entity
     */
    final public function add($entity): Entity
    {
        // Convert arrays to the Entity to set defaults and casts
        if (is_array($entity)) {
            $class  = static::ENTITY;
            $entity = new $class($entity);
        }

        // Check validation
        if (! $this->validate($entity->toArray())) {
            throw new UnexpectedValueException(static::ENTITY . ' failed validation: ' . implode(' ', $this->getErrors()));
        }
        $data = $this->protectFields($entity->toArray());

        // Add created and updated fields as necessary
        if ($this->useTimestamps) {
            if ($this->createdField !== null) {
                $data[$this->createdField] ??= FieldValue::serverTimestamp();
            }
            if ($this->updatedField !== null) {
                $data[$this->updatedField] ??= FieldValue::serverTimestamp();
            }
        }

        // If a primary key was supplied use set()
        if (isset($data[$this->primaryKey])) {
            $reference = $this->collection->document($data[$this->primaryKey]);
            $reference->set($data);
        }
        // Otherwise add the data and store the reference
        else {
            $reference = $this->collection->add($data);
        }

        $entity->document($reference);

        return $entity;
    }

    /**
     * Adds a new document from an Entity.
     *
     * @param array<string, mixed> $data
     *
     * @return Entity The locally-updated Entity
     */
    final public function update(Entity $entity, array $data): Entity
    {
        // Check validation
        if (! $this->validate($data)) {
            throw new UnexpectedValueException(static::ENTITY . ' failed validation: ' . implode(' ', $this->getErrors()));
        }

        if ([] === $data = $this->protectFields($data)) {
            throw new UnexpectedValueException('No data found for update.');
        }

        if (null === $reference = $entity->document()) {
            throw new UnexpectedValueException('Entity must exist before updating.');
        }

        // Add updated field as necessary
        if ($this->updatedField !== null) {
            $data[$this->updatedField] ??= FieldValue::serverTimestamp();
        }

        $paths = [];

        foreach ($data as $key => $value) {
            $entity->{$key} = $value;
            $paths[]        = [
                'path'  => $key,
                'value' => $value,
            ];
        }

        $reference->update($paths);

        return $entity;
    }

    /**
     * Removes a document.
     *
     * @param Entity|string $entity The Entity or its ID
     */
    final public function remove($entity): void
    {
        $reference = is_string($entity) ? $this->collection->document($entity) : $entity->document();

        $reference->delete();
    }

    /**
     * Fetches a Document into a new Entity by its reference or ID, if it exists.
     *
     * @param DocumentReference|string $reference
     */
    final public function get($reference): ?Entity
    {
        if (is_string($reference)) {
            if ($reference === '') {
                throw new InvalidArgumentException('ID parameter may not be empty.');
            }

            $reference = $this->collection->document($reference);
        }

        $snapshot = $reference->snapshot();

        return $snapshot->exists()
            ? $this->fromSnapshot($snapshot)
            : null;
    }

    /**
     * Returns a Traversable for each document in the collection as an Entity.
     *
     * @param CollectionReference|Query|null $source An alternate source of documents:
     *                                               - CollectionReference is an alternate collection (like top-level from grouped context)
     *                                               - Query is the default for collectionGroup() or a query method like "where()"
     *                                               - Null will use the current $collection property
     *
     * @return Traversable<Entity>
     */
    final public function list($source = null): Traversable
    {
        $source ??= $this->collection;

        foreach ($source->documents() as $snapshot) {
            if ($snapshot->exists()) {
                yield $this->fromSnapshot($snapshot);
            }
        }
    }

    /**
     * Converts a DocumentSnapshot into an ENTITY.
     * The snapshot must already exist.
     */
    final public function fromSnapshot(DocumentSnapshot $snapshot): Entity
    {
        $data = $snapshot->data();

        $data[$this->primaryKey] = $snapshot->id();

        if ($this->createdField !== null) {
            $data[$this->createdField] ??= $snapshot->createTime();
        }
        if ($this->updatedField !== null) {
            $data[$this->updatedField] ??= $snapshot->updateTime();
        }

        // Create the entity before validation to get defaults and casts
        $class  = static::ENTITY;
        $entity = new $class($data);
        $entity->document($snapshot->reference());

        // Check validation
        if (! $this->validate($entity->toArray(), false)) {
            throw new UnexpectedValueException(static::ENTITY . ' failed validation: ' . implode(' ', $this->getErrors()));
        }

        return $entity;
    }

    /**
     * Adds and returns a new fake Entity.
     *
     * @param array<string, mixed> $overrides
     */
    final public function make(array $overrides = []): Entity
    {
        return $this->add($this->fake($overrides));
    }

    /**
     * Provides pass-through access to the CollectionReference for
     * a curated selection of methods that return a Query suitable
     * for method chaining and eventual submission to "list()".
     *
     * @throws BadMethodCallException
     */
    final public function __call(string $name, array $arguments): Query
    {
        if (! in_array($name, self::QUERY_METHODS, true)) {
            throw new BadMethodCallException('Call to undefined method ' . static::class . '::' . $name);
        }

        return $this->collection->{$name}(...$arguments);
    }
}
