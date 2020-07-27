<?php namespace Tatter\Firebase;

use DateTimeZone;
use Google\Cloud\Core\Timestamp;
use Tatter\Firebase\Model;

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

	/**
	 * Intercept casts to add support for subcollections
	 *
	 * @param $value
	 * @param string $type
	 *
	 * @return mixed
	 * @throws \Exception
	 */

	protected function castAs($value, string $type)
	{
		// Check for a model request
		if (is_int(strpos($type, 'model')))
		{
			list(, $class) = explode(':', $type);

			if ($model = model($class))
			{
				if ($model instanceof Model)
				{
					return $model->setBuilder($value);
				}
			}
			elseif (strpos($type, '?') === 0)
			{
				return $value;
			}
			
			throw new \RuntimeException('Cast target must be a valid Firebase Model, received ' . $class);
		}
		
		return parent::castAs($value, $type);
	}
}
