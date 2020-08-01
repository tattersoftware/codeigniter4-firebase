<?php namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Fabricator;
use Tests\Support\Models\ColorModel;
use Tests\Support\Models\ProfileModel;

class FirestoreTestCase extends CIUnitTestCase
{
	use \Tatter\Firebase\Test\FirestoreTrait;

	/**
	 * UID of the Profile with a subcollection.
	 *
	 * @var string
	 */
	protected $profileUid;

	/**
	 * Methods to run during setUp.
	 *
	 * @var array of methods
	 */
	protected $setUpMethods = [
		'mockEmail',
		'mockSession',
		'clearFirestore',
		'seedDatabase',
	];

	/**
	 * Create some fake entries.
	 */
	protected function seedDatabase()
	{
		$firestore  = service('firebase')->firestore->database();
		$fabricator = new Fabricator(ColorModel::class);

		// Remove any orphaned subcollections
		foreach ($firestore->collectionGroup('colors')->documents() as $document)
		{
			$document->reference()->delete();
		}

		// Fake some profiles
		fake(ProfileModel::class);
		fake(ProfileModel::class);
		$profile = fake(ProfileModel::class);
		$this->profileUid = $profile->uid;

		// Add some Colors as a subcollection
		$collection = $firestore->collection('profiles')->document($profile->uid)->collection('colors');
		$collection->add((array) $fabricator->make());
		$collection->add((array) $fabricator->make());

		// Make a second subcollection for grouped testing
		$profile    = fake(ProfileModel::class);
		$collection = $firestore->collection('profiles')->document($profile->uid)->collection('colors');
		$collection->add((array) $fabricator->make());
	}
}
