<?php namespace Tatter\Firestore\Config;

use Kreait\Firebase\Factory;
use CodeIgniter\Config\BaseService;
use Google\Cloud\Firestore\FirestoreClient;

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
	public static function firebase($serviceAccount = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return static::getSharedInstance('firestore', $config);
		}

		// Determine autodetect vs. credential path
		if ($serviceAccount === null)
		{
			$factory = new Factory();
		}
		else
		{
			$factory = (new Factory())->withServiceAccount($serviceAccount);
		}

		return $factory;
	}
}
