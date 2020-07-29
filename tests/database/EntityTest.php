<?php

use CodeIgniter\Test\Fabricator;
use Google\Cloud\Firestore\CollectionReference;
use Tests\Support\FirestoreTestCase;
use Tests\Support\Models\ColorModel;
use Tests\Support\Models\ProfileModel;

class EntityTest extends FirestoreTestCase
{
	public function testGetsSubcollection()
	{
		$profile = model(ProfileModel::class)->find($this->profileUid);
		$result  = $profile->colors;

		$this->assertInstanceOf(CollectionReference::class, $result);
	}
}
