<?php namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;

class FirestoreTestCase extends CIUnitTestCase
{
	use \Tatter\Firebase\Test\FirestoreTrait;

	/**
	 * Methods to run during setUp.
	 *
	 * @var array of methods
	 */
	protected $setUpMethods = [
		'mockEmail',
		'mockSession',
		'clearFirestore',
	];
}
