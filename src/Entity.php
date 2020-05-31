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
			// Convert to a DateTime
			$value = $value->get();
			
			// IntlDateFormatter can't handle "Z" timezone so convert it UTC
			if ($value->getTimezone()->getName() === 'Z')
			{
				$value->setTimezone(new DateTimeZone('UTC'));
			}
		}

		return parent::mutateDate($value);
	}
}
