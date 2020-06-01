<?php namespace Tatter\Firebase;

use DateTimeZone;
use Google\Cloud\Core\Timestamp;

class Entity extends \CodeIgniter\Entity
{
	protected $primaryKey = 'uid';

	protected $dates = ['createdAt', 'updatedAt'];
	
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
}
