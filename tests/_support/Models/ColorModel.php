<?php namespace Tests\Support\Models;

use Tatter\Firebase\Model;
use Faker\Generator;

class ProfileModel extends Model
{
	protected $table      = 'profiles.colors';
	protected $primaryKey = 'uid';
	protected $returnType = 'object';

	protected $useTimestamps  = true;
	protected $skipValidation = true;

	protected $allowedFields = ['name', 'hex'];

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
			'name' => $faker->colorName,
			'hex'  => $faker->hexcolor,
		];
	}
}
