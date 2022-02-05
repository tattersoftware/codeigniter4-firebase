<?php

use Config\Services;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class ServiceTest extends TestCase
{
    public function testMissingKeyfile()
    {
        $keyfile = '/foo/bar/keyfile.json';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid service account');

        $firebase = Services::firebase($keyfile, false);
        $firebase->firestore->database();
    }

    public function testInvalidKeyfile()
    {
        $keyfile = SUPPORTPATH . 'keyfiles/invalid.json';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid service account');

        $firebase = Services::firebase($keyfile, false);
        $firebase->firestore->database();
    }

    public function testUnauthorizedKeyfile()
    {
        $keyfile = SUPPORTPATH . 'keyfiles/example.json';

        $this->expectException(AuthError::class);
        $this->expectExceptionMessage('key param');

        $firebase = Services::firebase($keyfile, false);
        $firebase->auth->getUser('nonexistantuser');
    }

    public function testInvalidArgument()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Property banana does not exist');

        Services::firebase()->banana; // @phpstan-ignore-line
    }

    public function testIsset()
    {
        firestore();
        $result = [];

        $result[] = isset(Services::firebase()->storage);
        $result[] = isset(Services::firebase()->firestore);
        $result[] = isset(Services::firebase()->banana);

        $this->assertSame([true, true, false], $result);
    }

    public function testCall()
    {
        $result = Services::firebase()->getDebugInfo();

        $this->assertIsArray($result);
        $this->assertSame('codeigniter4-test', $result['projectId']);
    }
}
