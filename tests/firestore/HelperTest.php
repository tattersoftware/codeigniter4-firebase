<?php

use Tests\Support\Collections\FruitCollection;
use Tests\Support\FirestoreTestCase;

/**
 * @internal
 */
final class HelperTest extends FirestoreTestCase
{
    public function testCollectionThrows()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid source supplied');

        collection(FruitCollection::class, 'fruits'); // @phpstan-ignore-line
    }

    public function testCollectionNull()
    {
        $collection = collection(FruitCollection::class);

        $this->assertInstanceOf(FruitCollection::class, $collection);
    }

    public function testCollectionEntity()
    {
        $fruit    = $this->collection->make();
        $expected = 'fruits/' . $fruit->id() . '/fruits';

        $collection = collection(FruitCollection::class, $fruit);

        $this->assertInstanceOf(FruitCollection::class, $collection);
        $this->assertSame($expected, $collection->path());
    }

    public function testCollectionDocument()
    {
        $fruit    = $this->collection->make();
        $expected = 'fruits/' . $fruit->id() . '/fruits';

        $collection = collection(FruitCollection::class, $fruit->document());

        $this->assertInstanceOf(FruitCollection::class, $collection);
        $this->assertSame($expected, $collection->path());
    }

    /**
     * @dataProvider uidProvider
     */
    public function testUid2Int(string $uid, int $expected)
    {
        $this->assertSame($expected, uid2int($uid));
    }

    public function uidProvider(): array
    {
        return [
            ['12345', 12345],
            ['a1234', 101234],
            ['abcde', 1_011_121_314],
            ['abcde1234567', 1_011_121_314],
            ['ZZZZZZZZZ', 6_161_616_161],
        ];
    }
}
