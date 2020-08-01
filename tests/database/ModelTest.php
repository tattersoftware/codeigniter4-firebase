<?php

use CodeIgniter\Test\Fabricator;
use Tests\Support\FirestoreTestCase;
use Tests\Support\Models\ColorModel;
use Tests\Support\Models\ProfileModel;

class ModelTest extends FirestoreTestCase
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
	}

	public function testCanFindByUid()
	{
		$profile = $this->fabricator->make();
		$uid     = $this->model->insert($profile);

		$result = $this->model->find($uid);

		$this->assertEquals($profile->lastName, $result->lastName);
	}

	public function testWorksWithFabricator()
	{
		$result = $this->fabricator->create();

		$this->assertIsString($result->uid);
	}

	public function testCanCountResults()
	{
		$result = $this->model->countAllResults();

		$this->assertIsInt($result);
	}

	public function testGroupedLoadsSubcollectionRows()
	{
		$result = model(ColorModel::class, false)->findAll();

		$this->assertCount(3, $result);
	}
}
