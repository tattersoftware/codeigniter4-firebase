<?php

use CodeIgniter\Test\Fabricator;
use Google\Cloud\Firestore\DocumentReference;
use Tests\Support\FirestoreTestCase;
use Tests\Support\Entities\Profile;
use Tests\Support\Models\ColorModel;
use Tests\Support\Models\ProfileModel;

class ModelTest extends FirestoreTestCase
{
	/**
	 * @var ProfileModel
	 */
	private $model;

	/**
	 * @var Fabricator
	 */
	private $fabricator;

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
		/** @var Profile $profile */
		$profile = $this->fabricator->make();
		$uid     = $this->model->insert($profile);

		$result = $this->model->find($uid);

		$this->assertEquals($profile->lastName, $result->lastName); // @phpstan-ignore-line
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
		$result = (new ColorModel())->findAll();

		$this->assertCount(3, $result);
	}

	public function testReferenceReturnsDocumentReference()
	{
		$result = $this->model->reference('abc123');

		$this->assertInstanceOf(DocumentReference::class, $result);
		$this->assertEquals('abc123', $result->id());
		$this->assertEquals('profiles/abc123', $result->path());
	}
}
