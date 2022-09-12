<?php

use CodeIgniter\I18n\Time;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Firestore\DocumentReference;
use Tests\Support\Collections\FruitCollection;
use Tests\Support\FirestoreTestCase;

/**
 * @internal
 */
final class EntityTest extends FirestoreTestCase
{
    public function testIdUsesDocumentId()
    {
        $fruit      = $this->collection->make();
        $fruit->uid = 'abc123';

        $this->assertSame($fruit->document()->id(), $fruit->id());
    }

    public function testIdUsesAttribute()
    {
        $fruit      = $this->collection->fake();
        $fruit->uid = 'abc123';

        $this->assertSame('abc123', $fruit->id());
    }

    public function testIdIsNull()
    {
        $fruit = $this->collection->fake();

        $this->assertNull($fruit->id());
    }

    public function testMutateDateSupportsTimestamp()
    {
        $time  = new Time('2020-02-02 02:02:02');
        $fruit = $this->collection->fake([
            'createdAt' => new Timestamp($time),
        ]);

        $result = $fruit->createdAt;

        $this->assertInstanceOf(Time::class, $result);
        $this->assertSame($time->getYear(), $result->getYear());
    }

    public function testMutateDateSupportsNull()
    {
        $fruit = $this->collection->fake([
            'createdAt' => null,
        ]);

        $result = $fruit->createdAt;

        $this->assertNull($result);
    }

    public function testSuperRequiresDocument()
    {
        $fruit = $this->collection->fake();

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Entity must exist before accessing parent.');

        $this->assertNull($fruit->super());
    }

    public function testSuperIsNull()
    {
        $fruit = $this->collection->make();

        $this->markTestSkipped('https://github.com/googleapis/google-cloud-php/pull/5492');

        // @phpstan-ignore-next-line
        $this->assertNull($fruit->super());
    }

    public function testSuperReturnsCollectionDocumentParent()
    {
        $fruit = $this->collection->make();
        $foods = collection(FruitCollection::class, $fruit);
        $food  = $foods->add(['name' => 'food']);

        $result = $food->super();

        $this->assertInstanceOf(DocumentReference::class, $result);
        $this->assertSame($fruit->id(), $result->id());
    }
}
