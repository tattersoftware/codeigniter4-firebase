<?php namespace Tatter\Firebase\Config;

use Tatter\Firebase\Firebase;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
	/**
	 * Returns the class wrapper for a Factory for the Firebase SDK
	 *
	 * @param mixed    $serviceAccount  Anything accepted by ServiceAccount::fromValue()
	 * @param boolean  $getShared
	 *
	 * @return \Tatter\Firebase\Firebase
	 */
	public static function firebase($serviceAccount = null, bool $getShared = true): Firebase
	{
		if ($getShared)
		{
			return static::getSharedInstance('firebase', $serviceAccount);
		}

		return new Firebase($serviceAccount);
	}
}
