<?php namespace Tests\Support\Models;

use Tatter\Firebase\Model;
use Faker\Generator;

class ProfileModel extends Model
{
	protected $table      = 'profiles';
	protected $primaryKey = 'uid';
	protected $returnType = 'object';

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
		return (object) [
			'firstName' => $faker->firstName,
			'lastName'  => $faker->lastName,
			'age'       => rand(5, 90),
			'weight'    => rand(110, 280),
		];
	}
}
