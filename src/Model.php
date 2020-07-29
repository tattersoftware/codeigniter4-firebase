<?php namespace Tatter\Firebase;

use Config\Services;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Exceptions\ModelException;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FieldValue;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\Query;
use Tatter\Firebase\Entity;

/**
 * Class Model
 *
 * This is a faux model provided for convenience so projects using an intermediary
 * version of this Firestore module can fake having a database driver.
 * Be sure you know the limitations of this model before using it.
 */
class Model
{
	/**
	 * Name of database table
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The table's primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'uid';

	/**
	 * The format that the results should be returned as.
	 * Will be overridden if the as* methods are used.
	 *
	 * @var string
	 */
	protected $returnType = '\Tatter\Firebase\Entity';

	/**
	 * If this model should use "softDeletes" and
	 * simply set a date when rows are deleted, or
	 * do hard deletes.
	 *
	 * @var boolean
	 */
	protected $useSoftDeletes = false;

	/**
	 * An array of field names that are allowed
	 * to be set by the user in inserts/updates.
	 *
	 * @var array
	 */
	protected $allowedFields = [];

	/**
	 * If true, will set created_at, and updated_at
	 * values during insert and update routines.
	 *
	 * @var boolean
	 */
	protected $useTimestamps = false;

	/**
	 * The type of column that created_at and updated_at
	 * are expected to be.
	 *
	 * Allowed: 'datetime', 'date', 'int'
	 *
	 * @var string
	 */
	protected $dateFormat = 'datetime';

	//--------------------------------------------------------------------

	/**
	 * The column used for insert timestamps
	 *
	 * @var string
	 */
	protected $createdField = 'createdAt';

	/**
	 * The column used for update timestamps
	 *
	 * @var string
	 */
	protected $updatedField = 'updatedAt';

	/**
	 * Used by withDeleted to override the
	 * model's softDelete setting.
	 *
	 * @var boolean
	 */
	protected $tempUseSoftDeletes;

	/**
	 * The column used to save soft delete state
	 *
	 * @var string
	 */
	protected $deletedField = 'deletedAt';

	/**
	 * Used by asArray and asObject to provide
	 * temporary overrides of model default.
	 *
	 * @var string
	 */
	protected $tempReturnType;

	/**
	 * Whether we should limit fields in inserts
	 * and updates to those available in $allowedFields or not.
	 *
	 * @var boolean
	 */
	protected $protectFields = true;

	/**
	 * Database Connection
	 *
	 * @var ConnectionInterface
	 */
	protected $db;

	/**
	 * Query Builder object
	 *
	 * @var BaseBuilder
	 */
	protected $builder;

	/**
	 * Array of document references from the last actual Firestore call
	 *
	 * @var array
	 */
	protected $documents;

	/**
	 * Rules used to validate data in insert, update, and save methods.
	 * The array must match the format of data passed to the Validation
	 * library.
	 *
	 * @var array
	 */
	protected $validationRules = [];

	/**
	 * Contains any custom error messages to be
	 * used during data validation.
	 *
	 * @var array
	 */
	protected $validationMessages = [];

	/**
	 * Skip the model's validation. Used in conjunction with skipValidation()
	 * to skip data validation for any future calls.
	 *
	 * @var boolean
	 */
	protected $skipValidation = false;

	/**
	 * Whether rules should be removed that do not exist
	 * in the passed in data. Used between inserts/updates.
	 *
	 * @var boolean
	 */
	protected $cleanValidationRules = true;

	/**
	 * Our validator instance.
	 *
	 * @var \CodeIgniter\Validation\Validation
	 */
	protected $validation;

	//--------------------------------------------------------------------

	/**
	 * Error messages from the last call
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Model constructor.
	 *
	 * @param FirestoreClient     $db
	 * @param ValidationInterface $validation
	 */
	public function __construct(FirestoreClient &$db = null, ValidationInterface $validation = null)
	{
		if ($db instanceof FirestoreClient)
		{
			$this->db = & $db;
		}
		else
		{
			$this->db = Services::firebase()->firestore->database();
		}
		
		$this->tempReturnType     = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		if (is_null($validation))
		{
			$validation = Services::validation(null, false);
		}

		$this->validation = $validation;
	}

	//--------------------------------------------------------------------
	// CORE COLLECTION
	//--------------------------------------------------------------------

	/**
	 * WHERE
	 *
	 * Adds a "where" to the query
	 *
	 * @param mixed   $str
	 * @param mixed   $value
	 *
	 * @return $this
	 */
	public function where($str, $value = null)
	{
		list($key, $op, $val) = $this->parseWhere($str);

		$value = $value ?? $val;

		$this->builder = $this->builder()->where($key, $op, $value);

		return $this;
	}

	/**
	 * WHERE IN
	 *
	 * Adds a "where in" to the query
	 *
	 * @param mixed   $key
	 * @param mixed   $values
	 *
	 * @return $this
	 */
	public function whereIn($key, $values)
	{
		$this->builder = $this->builder()->where($key, 'array-contains', $values);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the results of the query in array format and store them in $rowData.
	 *
	 * @param integer $limit  The limit clause
	 * @param integer $offset The offset clause
	 * @param boolean $reset  Do we want to clear query builder values?
	 *
	 * @return $this
	 */
	public function get(int $limit = null, int $offset = 0, bool $reset = true)
	{
		// Retrieve the documents from the collection
		$snapshot = $this->builder()->documents();

		// If nothing matched then we're done
		if ($snapshot->isEmpty())
		{
			$this->documents = [];
		}
		else
		{
			$this->documents = $snapshot->rows();
		}

		return $reset ? $this->reset() : $this;
	}

	//--------------------------------------------------------------------

	/**
	 * ORDER BY
	 *
	 * @param string  $orderBy
	 * @param string  $direction ASC, DESC or RANDOM
	 * @param boolean $escape
	 *
	 * @return $this
	 */
	public function orderBy(string $orderBy, string $direction = 'ASC', bool $escape = null)
	{
		$this->builder = $this->builder()->orderBy($orderBy, $direction);

		return $this;
	}

	/**
	 * LIMIT
	 *
	 * @param integer $value  LIMIT value
	 * @param integer $offset OFFSET value
	 *
	 * @return $this
	 */
	public function limit(int $value = null, ?int $offset = 0)
	{
		$this->builder = $this->builder()->limit($value);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Format the results of the query. Returns an array of
	 * individual data rows, which can be either an 'array', an
	 * 'object', or a custom class name.
	 *
	 * @param string $type The row type. Either 'array', 'object', or a class name to use
	 *
	 * @return array
	 */
	public function getResult(string $type = 'object'): array
	{
		// Bail on missing or empty returns
		if (empty($this->documents))
		{
			return [];
		}
		
		// Extract the actual data into arrays
		$result = [];
		foreach ($this->documents as $document)
		{
			// Get the meta fields
			$row = [
				$this->primaryKey   => $document->id(),
				$this->createdField => $document->createTime(),
				$this->updatedField => $document->updateTime(),
			];

			// Add the array of data
			$row = array_merge($row, $document->data());

			// Array requests are ready to go
			if ($type === 'array')
			{
				$result[] = $row;
			}
			if ($type === 'object')
			{
				$result[] = (object) $row;
			}
			// If it is an Entity then use the native constructor fill
			elseif (is_a($type, '\CodeIgniter\Entity', true))
			{
				$entity = new $type($row);

				// If it is our entity then inject the DocumentRerefence
				if ($entity instanceof Entity)
				{
					$entity->document($document->reference());
				}

				$result[] = $entity;
			}
			// Not sure what this will be but assign each property
			else
			{
				$object = new $type();
				foreach ($row as $key => $value)
				{
					$object->$key = $value;
				}
				$result[] = $object;
			}
		}

		return $result;
	}
	
	//--------------------------------------------------------------------
	// CRUD & FINDERS
	//--------------------------------------------------------------------

	/**
	 * Fetches the row of database from $this->table with a primary key
	 * matching $id.
	 *
	 * @param mixed|array|null $id One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	public function find($id = null)
	{
		if (is_array($id))
		{
			if ($this->tempUseSoftDeletes === true)
			{
				$this->where($this->deletedField, null);
			}

			$result = $this->whereIn($this->primaryKey, $id)->getResult($this->tempReturnType);
		}
		elseif (is_numeric($id) || is_string($id))
		{
			// Make sure we use the CollectionReference to get the Document directly
			if (($builder = $this->builder()) instanceof Query)
			{
				$builder = $this->db->collection($this->table);
			}

			$document = $builder->document($id)->snapshot();

			if (! $document->exists())
			{
				$result = null;
			}
			else
			{
				$this->documents = [$document];
				$result = $this->getResult($this->tempReturnType);
				$result = is_null($result) ? null : $result[0];
			}
		}
		else
		{
			return $this->findAll();
		}

		// Clear this execution's parameters
		$this->reset();

		return $result;
	}

	/**
	 * Works with the current Collection Reference instance to return
	 * all results, while optionally limiting them.
	 *
	 * @param integer $limit
	 * @param integer $offset
	 *
	 * @return array|null
	 */
	public function findAll(int $limit = 0, int $offset = 0)
	{
		if ($this->tempUseSoftDeletes === true)
		{
			$this->where($this->deletedField, null);
		}

		$result = $this->get()->getResult($this->tempReturnType);

		// Clear this execution's parameters
		$this->reset();

		return $result;	
	}

	//--------------------------------------------------------------------

	/**
	 * Inserts data into the current collection.
	 *
	 * @param array|object $data
	 * @param boolean      $returnID Whether insert ID should be returned or not.
	 *
	 * @return integer|string|boolean
	 */
	public function insert($data = null, bool $returnID = true)
	{
		if (empty($data))
		{
			throw DataException::forEmptyDataset('insert');
		}

		// Convert to an array
		if (is_object($data))
		{
			$data = self::classToArray($data);
		}

		// Check if an ID was provided
		$id = $data[$this->primaryKey] ?? false;

		// Must be called first so we don't
		// strip out created_at values.
		$data = $this->doProtectFields($data);

		// Make sure we have a fresh reference
		$this->reset();

		// If an ID was provided use 'set'
		if ($id)
		{
			$document = $this->builder()->document($id);
			$result = (bool) $document->set($data);
		}
		// Otherwise add the documentElement
		else
		{
			$document = $this->builder()->add($data);
			$result = (bool) $document;
		}

		// If insertion failed the we're done
		if (! $result)
		{
			return false;
		}
		
		// Save the insert ID
		$this->insertID = $document->id();

		// Set timestamps
		$timestamps = [];
		if ($this->useTimestamps && ! empty($this->createdField) && ! array_key_exists($this->createdField, $data))
		{
			$timestamps[] = ['path' => $this->createdField, 'value' => FieldValue::serverTimestamp()];
		}
		if ($this->useTimestamps && ! empty($this->updatedField) && ! array_key_exists($this->updatedField, $data))
		{
			$timestamps[] = ['path' => $this->updatedField, 'value' => FieldValue::serverTimestamp()];
		}
		if (! empty($timestamps))
		{
			$document->update($timestamps);
		}

		return $returnID ? $this->insertID : true;
	}

	/**
	 * Updates a document in the current collection.
	 *
	 * @param string $id
	 * @param array|object $data
	 *
	 * @return bool
	 */
	public function update(string $id, $data): bool
	{
		if (empty($data))
		{
			throw DataException::forEmptyDataset('insert');
		}

		// Convert to an array
		if (is_object($data))
		{
			$data = self::classToArray($data);
		}

		// Must be called first so we don't strip out updated_at values
		$data = $this->doProtectFields($data);

		// Update timestamp
		if ($this->useTimestamps && ! empty($this->updatedField) && ! array_key_exists($this->updatedField, $data))
		{
			$data[$this->updatedField] = FieldValue::serverTimestamp();
		}

		// Build the paths
		$paths = [];
		foreach ($data as $key => $value)
		{
			$paths[] = ['path' => $key, 'value' => $value];
		}
		
		// Prep the document
		$document = $this->builder()->document($id);

		// Clear this execution's parameters
		$this->reset();

		return (bool) $document->update($paths);
	}

	/**
	 * Deletes a document in the current collection.
	 * Does not remove subcollections!
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function delete(string $id): bool
	{
		if (empty($id))
		{
			throw DataException::forEmptyDataset('id');
		}

		// Prep the document
		$document = $this->builder()->document($id);

		// Clear this execution's parameters
		$this->reset();

		return (bool) $document->delete();
	}

	//--------------------------------------------------------------------
	// Utility
	//--------------------------------------------------------------------

	/**
	 * Resets model state, e.g. between completed queries.
	 *
	 * @return $this
	 */
	public function reset(): self
	{
		$this->builder(null, true);

		$this->tempReturnType     = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		return $this;
	}

	/**
	 * Provides a shared instance of the collection reference or a query in process.
	 *
	 * @param string $table
	 * @param bool $refresh  Resets the builder back to a clean CollectionReference
	 *
	 * @return CollectionReference|Query
	 * @throws \CodeIgniter\Exceptions\ModelException;
	 */
	protected function builder(string $table = null, bool $refresh = false)
	{
		if (! $refresh && ($this->builder instanceof CollectionReference || $this->builder instanceof Query))
		{
			return $this->builder;
		}

		// We're going to force a primary key to exist
		// so we don't have overly convoluted code,
		// and future features are likely to require them.
		if (empty($this->primaryKey))
		{
			throw ModelException::forNoPrimaryKey(get_class($this));
		}

		$table = empty($table) ? $this->table : $table;

		// Ensure we have a good client
		if (! $this->db instanceof FirestoreClient)
		{
			$this->db = Services::firebase()->firestore->database();
		}

		$this->builder = $this->db->collection($table);

		return $this->builder;
	}

	/**
	 * Sets a new $builder (usually a subcollection)
	 *
	 * @param CollectionReference|Query
	 *
	 * @return $this
	 */
	public function setBuilder($builder): self
	{
		$this->builder = $builder;

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Parses the first parameter to a where method into its logical parts
	 *
	 * @param string  $str
	 *
	 * @return array  [key, operator, value]
	 */
	protected function parseWhere(string $str): array
	{
		$parts = explode(' ', $str);
		
		switch (count($parts))
		{
			// where('name', 'Roland')
			case 1:
				return [$parts[0], '=', null];
			break;
			
			// where('age >', 999)
			case 2:
				return [$parts[0], $parts[1], null];
			break;
			
			// where('status != "bogus"')
			case 3:
				return [$parts[0], $parts[1], $parts[2]];
			break;
			
			default:
				throw \RuntimeException('Unable to parse where clause: ' . $str);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that only the fields that are allowed to be updated
	 * are in the data array.
	 *
	 * Used by insert() and update() to protect against mass assignment
	 * vulnerabilities.
	 *
	 * @param array $data
	 *
	 * @return array
	 * @throws \CodeIgniter\Database\Exceptions\DataException
	 */
	protected function doProtectFields(array $data): array
	{
		if ($this->protectFields === false)
		{
			return $data;
		}

		if (empty($this->allowedFields))
		{
			throw DataException::forInvalidAllowedFields(get_class($this));
		}

		if (is_array($data) && count($data))
		{
			foreach ($data as $key => $val)
			{
				if (! in_array($key, $this->allowedFields))
				{
					unset($data[$key]);
				}
			}
		}

		return $data;
	}

	/**
	 * Sets $useSoftDeletes value so that we can temporarily override
	 * the softdeletes settings. Can be used for all find* methods.
	 *
	 * @param boolean $val
	 *
	 * @return $this
	 */
	public function withDeleted($val = true): self
	{
		$this->tempUseSoftDeletes = ! $val;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Return the total number of results (safe up to medium-large datasets).
	 *
	 * @param boolean $reset
	 * @param boolean $test
	 *
	 * @return int
	 */
	public function countAllResults(bool $reset = true, bool $test = false): int
	{
		// Retrieve the documents from the collection
		$snapshot = $this->builder()->documents();

		return $snapshot->isEmpty() ? 0 : $snapshot->size();
	}

	//--------------------------------------------------------------------

	/**
	 * Takes a class and returns an array of it's public and protected
	 * properties as an array suitable for use in creates and updates.
	 *
	 * @param string|object $data
	 * @param string|null   $primaryKey
	 * @param string        $dateFormat
	 * @param boolean       $onlyChanged
	 *
	 * @return array
	 */
	public static function classToArray($data, $primaryKey = null, string $dateFormat = 'datetime'): array
	{
		if (method_exists($data, 'toRawArray'))
		{
			$properties = $data->toRawArray();
		}
		else
		{
			$properties = (array) $data;
		}

		return $properties;
	}


	/**
	 * Get and clear any error messsages
	 *
	 * @return array  Any error messages from the last operation
	 */
	public function errors(): array
	{
		$errors       = $this->errors;
		$this->errors = [];

		return $errors;
	}

	//--------------------------------------------------------------------

	/**
	 * Provide access to underlying properties consistent with CodeIgniter\Model.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get(string $name)
	{
		if (property_exists($this, $name))
		{
			return $this->{$name};
		}
		elseif (isset($this->db->$name))
		{
			return $this->db->$name;
		}
		elseif (isset($this->builder()->$name))
		{
			return $this->builder()->$name;
		}

		return null;
	}

	/**
	 * Provide access to underlying properties consistent with CodeIgniter\Model.
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function __isset(string $name): bool
	{
		if (property_exists($this, $name))
		{
			return true;
		}
		elseif (isset($this->db->$name))
		{
			return true;
		}
		elseif (isset($this->builder()->$name))
		{
			return true;
		}

		return false;
	}
}
