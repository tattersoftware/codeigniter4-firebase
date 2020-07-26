<?php

class ServiceTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}

	public function testMissingKeyfile()
	{
		$keyfile = '/foo/bar/keyfile.json';

		$this->expectException(\Kreait\Firebase\Exception\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid service account');

		$firebase = \Config\Services::firebase($keyfile, false);
		$firebase->firestore->database();
	}

	public function testInvalidKeyfile()
	{
		$keyfile = SUPPORTPATH . 'keyfiles/invalid.json';

		$this->expectException(\Kreait\Firebase\Exception\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid service account');

		$firebase = \Config\Services::firebase($keyfile, false);
		$firebase->firestore->database();
	}

	public function testUnauthorizedKeyfile()
	{
		$keyfile = SUPPORTPATH . 'keyfiles/example.json';

		$this->expectException(\Kreait\Firebase\Exception\Auth\AuthError::class);
		$this->expectExceptionMessage('supplied key param');

		$firebase = \Config\Services::firebase($keyfile, false);
		$firebase->auth->getUser('nonexistantuser');
	}
}
