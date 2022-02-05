<?php

namespace Tatter\Firebase\Test;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FirestoreClient;
use RuntimeException;

/**
 * Trait to add some helpful utilities for testing with Firestore.
 */
trait FirestoreTestTrait
{
    protected function setUpFirestoreTestTrait(): void
    {
        helper(['firestore']);
    }

    /**
     * Completely empty a Firestore database.
     *
     * @param bool $useEmulator For testing only.
     */
    protected function clearFirestore(bool $useEmulator = true)
    {
        if (ENVIRONMENT !== 'testing') {
            throw new RuntimeException('This feature is only available during testing.'); // @codeCoverageIgnore
        }

        // Get the shared instance
        $firestore = firestore();

        // Check for an emulated instance
        if (
            $useEmulator
            && ($firestoreEmulatorHost = getenv('FIRESTORE_EMULATOR_HOST'))
            && ($projectId = service('firebase')->getDebugInfo()['projectId'])
        ) {
            $deleteUrl = 'http://' . $firestoreEmulatorHost . '/emulator/v1/projects/' . $projectId . '/databases/(default)/documents';
            single_service('curlrequest')->delete($deleteUrl);

            return;
        }

        // Otherwise delete each collection
        foreach ($firestore->collections() as $collection) {
            $this->deleteCollection($collection, $firestore);
        }
    }

    /**
     * Remove an entire Firestore Collection, including subcollections.
     *
     * @param CollectionReference|string $collection
     */
    protected function deleteCollection($collection, ?FirestoreClient $firestore = null)
    {
        $firestore ??= firestore();

        // If a collection name was given then get the reference
        if (is_string($collection)) {
            $collection = $firestore->collection($collection);
        }

        // https://firebase.google.com/docs/firestore/manage-data/delete-data#collections
        $documents = $collection->limit(30)->documents();

        while (! $documents->isEmpty()) {
            foreach ($documents as $document) {
                foreach ($document->reference()->collections() as $subcollection) {
                    $this->deleteCollection($subcollection, $firestore);
                }

                $document->reference()->delete();
            }
            $documents = $collection->limit(30)->documents();
        }
    }
}
