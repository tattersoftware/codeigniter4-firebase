<?php

namespace Tests\Support\Models;

use Faker\Generator;
use Tatter\Firebase\Model;
use Tests\Support\Entities\Profile;

class WithCollectionsModel extends Model
{
    protected $table          = 'profiles';
    protected $primaryKey     = 'uid';
    protected $returnType     = 'Tests\Support\Entities\WithCollections';
    protected $useTimestamps  = true;
    protected $skipValidation = true;
    protected $allowedFields  = ['firstName', 'lastName', 'age', 'weight'];

    /**
     * Faked data for Fabricator.
     */
    public function fake(Generator &$faker): object
    {
        return new Profile([
            'firstName' => $faker->firstName,
            'lastName'  => $faker->lastName,
            'age'       => mt_rand(5, 90),
            'weight'    => mt_rand(110, 280),
        ]);
    }
}
