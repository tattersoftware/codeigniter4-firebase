<?php namespace Tatter\Firebase\Config;

use CodeIgniter\Config\BaseService;
use Tatter\Firebase\Firebase;

class Services extends BaseService
{
	/**
	 * Returns an authenticated Factory for the Firebase SDK
	 *
	 * @param mixed    $serviceAccount  Anything accepted by ServiceAccount::fromValue()
	 * @param boolean  $getShared
	 *
	 * @return Firebase
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
