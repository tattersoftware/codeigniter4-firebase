<?php

use CodeIgniter\Test\Fabricator;
use Google\Cloud\Firestore\CollectionReference;
use Tests\Support\FirestoreTestCase;
use Tests\Support\Entities\Profile;
use Tests\Support\Entities\WithCollections;
use Tests\Support\Models\ColorModel;
use Tests\Support\Models\ProfileModel;
use Tests\Support\Models\WithCollectionsModel;

class EntityTest extends FirestoreTestCase
{
	public function testGetsSubcollection()
	{
		$profile = model(ProfileModel::class)->find($this->profileUid);
		$result  = $profile->colors;

		$this->assertInstanceOf(CollectionReference::class, $result);
	}

	public function testUsesCollectionModel()
	{
		$profile = model(WithCollectionsModel::class)->find($this->profileUid);
		$result  = $profile->colors;

		$this->assertInstanceOf(ColorModel::class, $result);
	}

	public function testModelGetsRows()
	{
		$profile = model(WithCollectionsModel::class)->find($this->profileUid);
		$colors  = $profile->colors->findAll();

		$this->assertCount(2, $colors);
	}

	public function testCollectionInsertAddsRow()
	{
		$profile = model(WithCollectionsModel::class)->find($this->profileUid);
		$profile->colors->insert(['name' => 'gray', 'hex' => '#999999']);

		$colors = $profile->colors->findAll();
		$this->assertCount(3, $colors);
	}
}
