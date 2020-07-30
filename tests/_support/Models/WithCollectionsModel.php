<?php namespace Tests\Support\Models;

use Tatter\Firebase\Model;
use Tests\Support\Entities\Profile;
use Faker\Generator;

class WithCollectionsModel extends Model
{
	protected $table      = 'profiles';
	protected $primaryKey = 'uid';
	protected $returnType = 'Tests\Support\Entities\WithCollections';

	protected $useTimestamps  = true;
	protected $skipValidation = true;

	protected $allowedFields = ['firstName', 'lastName', 'age', 'weight'];

	/**
	 * Faked data for Fabricator.
	 *
	 * @param Generator $faker
	 *
	 * @return object
	 */
	public function fake(Generator &$faker): object
	{
		return new Profile([
			'firstName' => $faker->firstName,
			'lastName'  => $faker->lastName,
			'age'       => rand(5, 90),
			'weight'    => rand(110, 280),
		]);
	}
}
