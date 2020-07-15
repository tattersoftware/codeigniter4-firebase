<?php

use CodeIgniter\Test\Fabricator;
use Tests\Support\Models\ProfileModel;

class ModelTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->model      = new ProfileModel();
		$this->fabricator = new Fabricator($this->model);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		$this->model->reset();
		unset($this->model, $this->fabricator);
	}

	public function testCanInsert()
	{
		$profile = $this->fabricator->make();

		$result = $this->model->insert($profile);
		$this->assertIsString($result);
		
		$this->modelCache[] = [ProfileModel::class, $result];
	}

	public function testCanFindByUid()
	{
		$profile = $this->fabricator->make();
		$uid     = $this->model->insert($profile);

		$this->modelCache[] = [ProfileModel::class, $uid];

		$result = $this->model->find($uid);

		$this->assertEquals($profile->lastName, $result->lastName);
	}

	public function testWorksWithFabricator()
	{
		$result = $this->fabricator->create();

		$this->assertIsString($result->uid);
		
		$this->modelCache[] = [ProfileModel::class, $result->uid];
	}

/* This test is crashing PHP!
	public function testCanCountResults()
	{
		$result = $this->model->countAllResults();

		$this->assertIsInt($result);
	}
*/
}
