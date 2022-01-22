<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class ServiceTest extends CIUnitTestCase
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
}
