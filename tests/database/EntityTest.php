<?php

use CodeIgniter\Test\Fabricator;
use Tests\Support\FirestoreTestCase;
use Tests\Support\Models\ColorModel;
use Tests\Support\Models\ProfileModel;

class EntityTest extends FirestoreTestCase
{
	public function testSubcollectionCastsAsModel()
	{
		$db     = service('firebase')->firestore->database();
		$colors = model(ColorModel::class);

		// Create a document with a subcollection
		$profile    = fake(ProfileModel::class);
		$document   = $db->collection('profiles')->document($profile->uid);
		$collection = $document->collection('colors');

		$collection->add(['name' => 'white', 'hex' => '#ffffff']);
		$collection->add(['name' => 'black', 'hex' => '#000000']);

		$profile = model(ProfileModel::class)->find($profile->uid);
		dd($profile);
	}
}
