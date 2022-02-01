<?php

use Tests\Support\Collections\FruitCollection;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class HelperTest extends TestCase
{
    public function testCollection()
    {
        $collection = collection(FruitCollection::class);

        $this->assertInstanceOf(FruitCollection::class, $collection);
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
