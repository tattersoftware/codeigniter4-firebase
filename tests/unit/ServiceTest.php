<?php

class ServiceTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->config = new \Tatter\Firestore\Config\Firestore();
	}

	public function testMissingKeyfile()
	{
		$this->config->keyfile = '/foo/bar/keyfile.json';

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Keyfile missing from');

        $firestore = \Config\Services::firestore($this->config);
	}

	public function testInvalidKeyfile()
	{
		$this->config->keyfile = MODULESUPPORTPATH . 'keyfiles/invalid.json';

		$this->expectException(\Google\Cloud\Core\Exception\GoogleException::class);
		$this->expectExceptionMessage('Given keyfile at path');

        $firestore = \Config\Services::firestore($this->config);
	}
}
