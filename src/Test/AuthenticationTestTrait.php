<?php

namespace Tatter\Firebase\Test;

use Faker\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use RuntimeException;

/**
 * Trait to use when testing requires a valid Firebase user account.
 */
trait AuthenticationTestTrait
{
    /**
     * Array of created User account UIDs.
     *
     * @var string[]
     */
    protected array $firebaseUserCache = [];

    /**
     * Creates a random Firebase UserRecord.
     *
     * @param array<string, string> $data Data to use instead of generating randomly
     */
    protected function createFirebaseUser(array $data = []): UserRecord
    {
        $faker = Factory::create(config('App')->defaultLocale);

        $data['email'] ??= $faker->email;
        $data['displayName'] ??= $faker->name;
        // $data['phoneNumber'] ??= $faker->phoneNumber; // Faker's phone numbers are not compatible with Google
        $data['password'] ??= bin2hex(random_bytes(16));

        // Create the user account
        $user = service('firebase')->auth->createUser($data);

        // Store it in the cache for removal later
        $this->firebaseUserCache[] = $user->uid;

        return $user;
    }

    /**
     * Removes a Firebase Auth user.
     */
    protected function removeFirebaseUser(string $uid)
    {
        try {
            service('firebase')->auth->deleteUser($uid);
        } catch (UserNotFound $e) {
        }

        $this->firebaseUserCache = array_diff($this->firebaseUserCache, [$uid]);
    }

    /**
     * Removes all accounts from Firebase Auth.
     */
    protected function clearFirebaseUsers()
    {
        if (ENVIRONMENT !== 'testing') {
            throw new RuntimeException('This feature is only available during testing.');  // @codeCoverageIgnore
        }

        foreach (service('firebase')->auth->listUsers() as $user) {
            $this->removeFirebaseUser($user->uid);
        }

        $this->firebaseUserCache = [];
    }
}
