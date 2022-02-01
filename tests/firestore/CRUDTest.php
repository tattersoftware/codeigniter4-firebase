<?php

use Tatter\Firebase\Firestore\Entity;
use Tests\Support\Entities\Fruit;
use Tests\Support\FirestoreTestCase;

/**
 * @internal
 */
final class CRUDTest extends FirestoreTestCase
{
    public function testAddArray()
    {
        $data = [
            'name'   => 'banana',
            'taste'  => 'sweet',
            'weight' => 20,
        ];

        $result = $this->collection->add($data);
        $this->assertInstanceOf(Entity::class, $result);

        $snapshot = $this->firestore->collection('fruits')->document($result->id())->snapshot();
        $this->assertSame('banana', $snapshot->data()['name']);
    }

    public function testAddEntity()
    {
        $data = [
            'name'   => 'banana',
            'taste'  => 'sweet',
            'weight' => 20,
        ];

        $result = $this->collection->add(new Fruit($data));
        $this->assertInstanceOf(Entity::class, $result);

        $snapshot = $this->firestore->collection('fruits')->document($result->id())->snapshot();
        $this->assertSame('banana', $snapshot->data()['name']);
    }

    public function testAddWithPrimaryKey()
    {
        $data = [
            'uid'    => 'abcdefg12345678',
            'name'   => 'apple',
            'taste'  => 'tart',
            'weight' => 15,
        ];

        $result = $this->collection->add($data);
        $this->assertInstanceOf(Entity::class, $result);

        $entity = $this->collection->get('abcdefg12345678');
        $this->assertSame('apple', $entity->name);
    }

    public function testAddFails()
    {
        $fruit = $this->collection->fake(['name' => '']);

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Tests\Support\Entities\Fruit failed validation: The name field is required');

        $this->collection->add($fruit);
    }

    public function testGetReference()
    {
        $reference = $this->firestore->collection('fruits')->add([
            'name'   => 'banana',
            'taste'  => 'sweet',
            'weight' => 20,
        ]);

        $result = $this->collection->get($reference);

        $this->assertInstanceOf(Entity::class, $result);
        $this->assertSame('banana', $result->name);
    }

    public function testGetString()
    {
        $reference = $this->firestore->collection('fruits')->add([
            'name'   => 'banana',
            'taste'  => 'sweet',
            'weight' => 20,
        ]);

        $result = $this->collection->get($reference->id());

        $this->assertInstanceOf(Entity::class, $result);
        $this->assertSame('banana', $result->name);
    }

    public function testGetStringFails()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('ID parameter may not be empty');

        $this->collection->get('');
    }

    public function testRemove()
    {
        $fruit = $this->collection->make();

        $this->collection->remove($fruit);

        $snapshot = $this->firestore->collection('fruits')->document($fruit->id())->snapshot();

        $this->assertFalse($snapshot->exists());
    }

    public function testRemoveString()
    {
        $fruit = $this->collection->make();

        $this->collection->remove($fruit->id());

        $snapshot = $this->firestore->collection('fruits')->document($fruit->id())->snapshot();

        $this->assertFalse($snapshot->exists());
    }

    public function testUpdate()
    {
        $fruit = $this->collection->make();

        $result = $this->collection->update($fruit, ['name' => 'banana']);
        $this->assertSame('banana', $result->name);

        $entity = $this->collection->get($fruit->id());
        $this->assertSame('banana', $entity->name);
    }

    public function testUpdateFailsValidation()
    {
        $fruit = $this->collection->fake();

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Tests\Support\Entities\Fruit failed validation: The name field is required');

        $this->collection->update($fruit, ['name' => '']);
    }

    public function testUpdateFailsMissingData()
    {
        $fruit = $this->collection->fake();

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('No data found for update');

        $this->collection->update($fruit, ['not_allowed' => 42]);
    }

    public function testUpdateFailsNonexistent()
    {
        $fruit = $this->collection->fake();

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Entity must exist before updating');

        $this->collection->update($fruit, ['name' => 'foo']);
    }

    public function testList()
    {
        $this->firestore->collection('fruits')->add([
            'name'   => 'banana',
            'taste'  => 'sweet',
            'weight' => 20,
        ]);
        $this->firestore->collection('fruits')->add([
            'name'   => 'apple',
            'taste'  => 'tangy',
            'weight' => 15,
        ]);

        $results  = [];
        $expected = [
            'apple'  => 15,
            'banana' => 20,
        ];

        foreach ($this->collection->list() as $fruit) {
            $this->assertInstanceOf(Fruit::class, $fruit);
            $results[$fruit->name] = $fruit->weight;
        }
        ksort($results);

        $this->assertSame($expected, $results);
    }

    public function testListUsesParameter()
    {
        $this->firestore->collection('vegetables')->add([
            'name'   => 'banana',
            'taste'  => 'sweet',
            'weight' => 20,
        ]);
        $this->firestore->collection('vegetables')->add([
            'name'   => 'apple',
            'taste'  => 'tangy',
            'weight' => 15,
        ]);

        $results  = [];
        $expected = [
            'apple'  => 15,
            'banana' => 20,
        ];

        $collection = firestore()->collection('vegetables');

        foreach ($this->collection->list($collection) as $fruit) {
            $this->assertInstanceOf(Fruit::class, $fruit);
            $results[$fruit->name] = $fruit->weight;
        }
        ksort($results);

        $this->assertSame($expected, $results);
    }
}
