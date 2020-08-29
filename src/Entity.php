<?php namespace Tatter\Firebase;

use DateTimeZone;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Firestore\DocumentReference;
use Tatter\Firebase\Model;

class Entity extends \CodeIgniter\Entity
{
	protected $primaryKey = 'uid';

	protected $dates = ['createdAt', 'updatedAt'];

	/**
	 * The originating Document from Firestore (needed for subcollections, etc)
	 *
	 * @var DocumentReference|null
	 */
	protected $document;

	/**
	 * Array of subcollections supported by this entity. Actual
	 * references may or may not exist with documents. Keys are the
	 * name of the collection and the values are the model to use
	 * when handling them, or null for raw Firestore.
	 *
	 * @var array|null of name => model|null
	 */
	protected $collections;

	/**
	 * Converts the given item into a \CodeIgniter\I18n\Time object.
	 * Adds support for Google\Cloud\Core\Timestamp
	 *
	 * @param Timestamp|\CodeIgniter\I18n\Time $value
	 *
	 * @return \CodeIgniter\I18n\Time
	 * @throws \Exception
	 */
	protected function mutateDate($value)
	{
		if ($value instanceof Timestamp)
		{
			// Convert to an int timestamp
			$value = $value->formatForApi()['seconds'];
		}

		return parent::mutateDate($value);
	}

	/**
	 * Set/get this Entity's originating document reference. Avoids collision
	 * with normal attribute get/set magic methods.
	 *
	 * @param DocumentReference|null $document
	 *
	 * @return DocumentReference
	 */
	public function document(DocumentReference $document = null): ?DocumentReference
	{
		if (! is_null($document))
		{
			$this->document = $document;
		}

		return $this->document;
	}

	/**
	 * Get any subcollections from this entity's document.
	 *
	 * @return array
	 */
	public function collections(): array
	{
		if (is_null($this->collections) && $document = $this->document())
		{
			$this->collections = [];

			foreach ($document->collections() as $collection)
			{
				$this->collections[$collection->id()] = $collection;
			}
		}

		return $this->collections ?? [];
	}

	/**
	 * Check missing attributes for a matching collection.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function __get(string $key)
	{
		$result = parent::__get($key);

		if ($result !== null)
		{
			return $result;
		}

		// Check for a matching collection
		if (array_key_exists($key, $this->collections()) && $this->document())
		{
			// If there's nothing there yet then get a new CollectionReference
			if (is_null($this->collections[$key]))
			{
				$this->collections[$key] = $this->document()->collection($key);
			}
			// If it is a model then load the model and set the builder
			elseif (is_string($this->collections[$key]))
			{
				$collection = $this->document()->collection($key);
				$this->collections[$key] = model($this->collections[$key])->setBuilder($collection);
			}

			return $this->collections[$key];
		}

		return null;
	}
}
