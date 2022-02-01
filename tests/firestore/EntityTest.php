<?php

use CodeIgniter\I18n\Time;
use Google\Cloud\Core\Timestamp;
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
}
