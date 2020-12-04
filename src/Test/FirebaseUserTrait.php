<?php namespace Tatter\Firebase\Test;

use CodeIgniter\Test\Fabricator;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\UserRecord;
use Tatter\Firebase\Model;

/**
 * Trait to use when testing requires a valid Firebase user account.
 */
trait FirebaseUserTrait
{
	/**
	 * Instance of the Firebase SDK.
	 *
	 * @var Auth|null
	 */
	protected static $auth;

	/**
	 * Fabricator for generating faux content.
	 *
	 * @var Fabricator|null
	 */
	protected static $fabricator;

	/**
	 * Array of user account UIDs to be removed.
	 *
	 * @var array
	 */
	protected $firebaseUserCache = [];

	/**
	 * Ensures the static instances are loaded.
	 *
	 * @return $this
	 */
	protected function initAuth(): self
	{
		if (! self::$auth)
		{
			self::$auth = service('firebase')->auth;
		}

		if (! self::$fabricator)
		{
			self::$fabricator = new Fabricator('Tatter\Firebase\Model', [
				'email'       => 'email',
				'displayName' => 'name',
				//'phoneNumber' => 'phoneNumber', // Faker phone numbers aren't currently acceptable
			]);
		}

		return $this;
	}

	/**
	 * Creates a random Firebase Auth user on-the-fly.
	 *
	 * @param array|null $overrides  Overriding data to use with the Fabricator
	 *
	 * @return UserRecord
	 */
	protected function createFirebaseUser(array $overrides = null): UserRecord
	{
		$this->initAuth();

		if ($overrides)
		{
			self::$fabricator->setOverrides($overrides, false);
		}

		// Generate random data from the fabricator
		$data = self::$fabricator->makeArray();
		
		// Make sure there is a password
		if (empty($data['password']))
		{
			$data['password'] = bin2hex(random_bytes(16));
		}

		// Create the user account
		$user = self::$auth->createUser($data);
		
		// Store it in the cache for removal later
		$this->firebaseUserCache[] = $user->uid;

		return $user;
	}

	/**
	 * Removes a Firebase Auth user.
	 *
	 * @param string $uid
	 */
	protected function removeFirebaseUser(string $uid)
	{
		$this->initAuth();

		self::$auth->deleteUser($uid);

		$this->firebaseUserCache = array_diff($this->firebaseUserCache, [$uid]);
	}

	/**
	 * Removes all accounts from Firebase Auth.
	 */
	protected function clearFirebaseAuth()
	{
		if (ENVIRONMENT !== 'testing')
		{
			throw new \RuntimeException('This feature is only available during testing.'); 
		}

		$this->initAuth();

		foreach (self::$auth->listUsers() as $user)
		{
			self::$auth->deleteUser($user->uid);
		}

		$this->firebaseUserCache = [];
	}

	/**
	 * Removes any Firebase Auth users in the cache.
	 */
	protected function firebaseUserTearDown()
	{
		foreach ($this->firebaseUserCache as $uid)
		{
			$this->removeFirebaseUser($uid);
		}
	}
}
