<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CallerTest extends CIUnitTestCase
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
