<?php

use CodeIgniter\Config\Factories;
use Google\Cloud\Firestore\FirestoreClient;
use Tatter\Firebase\Firestore\Collection;

if (! function_exists('collection')) {
    /**
     * Convenience method for getting Collections from Factories.
     * Mimics the framework's "model()" function.
     *
     * @template T of Collection
     *
     * @param class-string<T> $name
     *
     * @return T
     */
    function collection(string $name, bool $getShared = true, ?FirestoreClient $firestore = null): Collection
    {
        // @phpstan-ignore-next-line
        return Factories::collections($name, [
            'path'       => 'collections',
            'instanceOf' => Collection::class,
            'getShared'  => $getShared,
            'preferApp'  => true,
        ], $firestore);
    }
}
if (! function_exists('firestore')) {
    /**
     * A convenience method to return the current
     * Firestore shared connection instance.
     */
    function firestore(): FirestoreClient
    {
        return service('firebase')->firestore->database();
    }
}
if (! function_exists('uid2int')) {
    /**
     * Converts a UID to a (probably) unique integer ID.
     * Useful for bridging Document UIDs to libraries that
     * expect an int primary key.
     */
    function uid2int(string $uid): int
    {
        $index = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Build the result one index at a time
        $result = '';

        for ($i = 0; $i < 5; $i++) {
            $result .= strpos($index, $uid[$i]);
        }

        return (int) $result;
    }
}
