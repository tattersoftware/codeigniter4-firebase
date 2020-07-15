<?php namespace Tests\Support;

use CodeIgniter\Test\CIDatabaseTestCase;

class DatabaseTestCase extends CIDatabaseTestCase
{
	/**
	 * Cache of rows to be removed during tearDown.
	 *
	 * @var array of [model, ID]
	 */
	protected $modelCache = [];

	/**
	 * Methods to run during tearDown.
	 *
	 * @var array of methods
	 */
	protected $tearDownMethods = ['removeCache'];

    /**
     * Removes any test items in the cache.
     */
    protected function removeCache()
	{
		foreach ($this->modelCache as $row)
		{
			model($row[0])->delete($row[1]);
		}
	}
}
