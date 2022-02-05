<?php

use Tests\Support\TestCase;

/**
 * @internal
 */
final class CallerTest extends TestCase
{
    public function testGetErrors()
    {
        $caller = service('firebase')->caller;

        $this->assertSame([], $caller->getErrors());
    }

    public function testSetUid()
    {
        $caller = service('firebase')->caller;

        $caller->setUid('banana');
        $result = $this->getPrivateProperty($caller, 'uid');

        $this->assertSame('banana', $result);
    }
}
