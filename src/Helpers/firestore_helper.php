<?php

use CodeIgniter\Config\Factories;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\FirestoreClient;
use Tatter\Firebase\Firestore\Collection;
use Tatter\Firebase\Firestore\Entity;

if (! function_exists('collection')) {
    /**
     * Convenience method for loading Collections from Factories.
     * Mimics the framework's "model()" function.
     *
     * @template T of Collection
     *
     * @param class-string<T>                                   $class  Class name of the Collection to locate
     * @param CollectionReference|DocumentReference|Entity|null $source A Firestore component to initialize the (sub)collection:
     *                                                                  - CollectionReference is an explicit (sub)collection
     *                                                                  - DocumentReference or Entity will become the parent for a subcollection
     *                                                                  - Null will use the name to reference a top-level collection
     *
     * @throws InvalidArgumentException
     *
     * @return T
     */
    function collection(string $class, $source = null): Collection
    {
        if ($source instanceof Entity) {
            $source = $source->document();
        }

        // Determine the actual CollectionReference
        if ($source === null || $source instanceof CollectionReference) {
            $collection = $source;
        } elseif ($source instanceof DocumentReference) {
            $collection = $source->collection($class::NAME);
        } else {
            throw new InvalidArgumentException('Invalid source supplied.');
        }

        // @phpstan-ignore-next-line
        return Factories::collections($class, [
            'path'       => 'collections',
            'instanceOf' => Collection::class,
            'getShared'  => $collection === null,
            'preferApp'  => true,
        ], $collection);
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
