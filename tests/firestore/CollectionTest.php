<?php

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\Query;
use Tatter\Firebase\Firestore\Collection;
use Tatter\Firebase\Firestore\Entity;
use Tests\Support\Collections\FruitCollection;
use Tests\Support\Entities\Fruit;
use Tests\Support\FirestoreTestCase;

/**
 * @internal
 */
final class CollectionTest extends FirestoreTestCase
{
    public function testContructorThrows()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid source supplied');

        new FruitCollection('fruits'); // @phpstan-ignore-line
    }

    public function testConstructorUsesCollection()
    {
        $fruits = firestore()->collection('fruits');
        $result = (new FruitCollection($fruits))->collection();

        $this->assertSame($fruits, $result);
    }

    public function testCollection()
    {
        $result = $this->collection->collection();

        $this->assertInstanceOf(CollectionReference::class, $result);
        $this->assertSame($this->collection->id(), $result->id());
    }

    public function testId()
    {
        $result = $this->collection->id();

        $this->assertSame('fruits', $result);
    }

    public function testName()
    {
        $result = $this->collection->name();

        $this->assertSame('projects/codeigniter4-test/databases/(default)/documents/fruits', $result);
    }

    public function testPath()
    {
        $result = $this->collection->path();

        $this->assertSame('fruits', $result);
    }

    public function testParent()
    {
        // Create a new collection as a subcollection
        $fruit      = $this->collection->make();
        $collection = new FruitCollection($fruit);

        $result = $collection->parent();

        $this->assertInstanceOf(DocumentReference::class, $result);
        $this->assertSame($fruit->id(), $result->id());
    }

    public function testParentNull()
    {
        $result = $this->collection->parent();

        $this->assertNull($result);
    }

    public function testFromSnapshot()
    {
        $reference = $this->firestore->collection('fruits')->add([
            'name'   => 'banana',
            'taste'  => 'sweet',
            'weight' => 20,
        ]);

        $banana = $this->collection->fromSnapshot($reference->snapshot());

        $this->assertInstanceOf(Entity::class, $banana);
        $this->assertSame('banana', $banana->name);
    }

    public function testFakeReturnsEntity()
    {
        $collection             = new class () extends Collection {
            public const NAME   = 'fruits';
            public const ENTITY = Fruit::class;
        };

        $result = $collection->fake();

        $this->assertInstanceOf(Fruit::class, $result);
    }

    public function testCollectionGrouping()
    {
        // Create two new collections with "fruits" subcollections
        $fruit = $this->collection->fake(['name' => 'blueberry']);
        $food  = firestore()->collection('ingestibles')->add(['name' => 'food']);
        $food->collection('fruits')->add($fruit->toArray());

        $fruit   = $this->collection->fake(['name' => 'peach']);
        $platter = firestore()->collection('parties')->add(['name' => 'platter']);
        $platter->collection('fruits')->add($fruit->toArray());

        $group  = firestore()->collectionGroup('fruits');
        $fruits = $this->collection->list($group);
        $result = iterator_to_array($fruits);

        $this->assertCount(2, $result);
        $this->assertSame(['blueberry', 'peach'], array_column($result, 'name'));
    }

    public function testCallThrows()
    {
        $this->expectException('BadMethodCallException');
        $this->expectExceptionMessage('Call to undefined method');

        $this->collection->newDocument();
    }

    public function testCallReturnsQuery()
    {
        $result = $this->collection->limit(1);

        $this->assertInstanceOf(Query::class, $result);
    }

    public function testListUsesQuery()
    {
        $this->collection->make(['name' => 'banana']);
        $this->collection->make();
        $this->collection->make();

        $result = $this->collection->list();
        $this->assertCount(3, $result);

        $query  = $this->collection->where('name', '=', 'banana');
        $result = $this->collection->list($query);
        $this->assertCount(1, $result);
    }
}
