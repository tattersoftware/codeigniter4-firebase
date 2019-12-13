<?php namespace Tatter\Firebase\Config;

use CodeIgniter\Config\BaseService;
use Tatter\Firebase\Firebase;

class Services extends BaseService
{
	/**
	 * Returns an authenticated Factory for the Firebase SDK
	 *
	 * @param \Config\App $config
	 * @param boolean     $getShared
	 *
	 * @return \CodeIgniter\HTTP\CLIRequest
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
