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
		// Fake some profiles
		fake(ProfileModel::class);
		fake(ProfileModel::class);
		$profile = fake(ProfileModel::class);
		$this->profileUid = $profile->uid;

		// Add some Colors as a subcollection
		$collection = service('firebase')->firestore->database()->collection('profiles')->document($profile->uid)->collection('colors');
		$fabricator = new Fabricator(ColorModel::class);

		$collection->add((array) $fabricator->make());
		$collection->add((array) $fabricator->make());
	}
}
