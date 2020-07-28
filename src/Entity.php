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
	 * @var array of name => model|null
	 */
	protected $collections = [];
	
	/**
	 * Converts the given item into a \CodeIgniter\I18n\Time object.
	 * Adds support for Google\Cloud\Core\Timestamp
	 *
	 * @param $value
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
	protected function document(DocumentReference $document): DocumentReference
	{
		if (! is_null($document))
		{
			$this->document = $document;
		}
		return $this->document;
	}
}
