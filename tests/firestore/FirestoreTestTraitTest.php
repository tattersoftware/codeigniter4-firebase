<?php

use Tests\Support\FirestoreTestCase;

/**
 * @internal
 */
final class FirestoreTestTraitTest extends FirestoreTestCase
{
    public function testDeleteCollection()
    {
        $this->collection->make();
        $collections = firestore()->collections();
        $this->assertCount(1, $collections);

        $this->deleteCollection('fruits');
        $collections = firestore()->collections();
        $this->assertCount(0, $collections);
    }

    public function testDeleteCollectionRemovesSubcollections()
    {
        $fruit = $this->collection->make();
        $fruit->document()->collection('berries')->add(['name' => 'raspberry']);

        $collections = firestore()->collections();
        $this->assertCount(1, $collections);
        $subs = $fruit->document()->collections();
        $this->assertCount(1, $subs);

        $this->deleteCollection('fruits');

        $subs = $fruit->document()->collections();
        $this->assertCount(0, $subs);
    }

    /**
     * @dataProvider useEmulatorProvider
     */
    public function testClearFirestore(bool $useEmulator)
    {
        $this->collection->make();
        $collections = firestore()->collections();
        $this->assertCount(1, $collections);

        $this->clearFirestore($useEmulator);
        $collections = firestore()->collections();
        $this->assertCount(0, $collections);
    }

    public function useEmulatorProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
