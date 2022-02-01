<?php

use Tests\Support\Entities\Fruit;
use Tests\Support\FirestoreTestCase;

/**
 * @internal
 */
final class ValidationTraitTest extends FirestoreTestCase
{
    public function testValidateArray()
    {
        $fruit    = $this->collection->fake(['name' => '']);
        $expected = [
            'name' => 'The name field is required.',
        ];

        $this->collection->validate($fruit->toArray());

        $this->assertSame($expected, $this->collection->getErrors());
    }

    public function testSkipValidation()
    {
        $fruit = $this->collection->fake(['name' => '']);

        $this->collection->skipValidation()->add($fruit);

        $this->assertSame([], $this->collection->getErrors());
    }

    public function testFromSnapshotFailsValidation()
    {
        $reference = $this->firestore->collection('fruits')->add([
            'taste'  => 'sour',
            'weight' => 50,
        ]);

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage(Fruit::class . ' failed validation: The name field is required.');

        $this->collection->fromSnapshot($reference->snapshot());
    }

    public function testNotProtectFields()
    {
        $this->setPrivateProperty($this->collection, 'protectFields', false);
        $fruit = $this->collection->fake(['foo' => 'bar']);

        $result = $this->collection->add($fruit->toArray());

        $this->assertSame('bar', $result->foo);
    }

    public function testNoAllowedFields()
    {
        $fruit = $this->collection->fake();
        $this->setPrivateProperty($this->collection, 'allowedFields', []);

        $this->expectException('DomainException');
        $this->expectExceptionMessage('You must define the "allowedFields" to use field protection');

        $this->collection->add($fruit->toArray());
    }
}
