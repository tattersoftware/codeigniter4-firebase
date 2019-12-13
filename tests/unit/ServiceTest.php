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

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Keyfile missing from');

        $firebase = \Config\Services::firebase($keyfile);
	}

	public function testInvalidKeyfile()
	{
		$keyfile = MODULESUPPORTPATH . 'keyfiles/invalid.json';

		$this->expectException(\Google\Cloud\Core\Exception\GoogleException::class);
		$this->expectExceptionMessage('Given keyfile at path');

        $firebase = \Config\Services::firebase($keyfile);
	}
}
