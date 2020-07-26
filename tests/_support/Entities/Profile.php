<?php namespace Tests\Support\Entities;

use Tatter\Firebase\Entity;

class Profile extends Entity
{
	protected $casts = [
		'colors' => 'model:Tests\Support\Models\ColorModel',
		'age'    => 'int',
		'weight' => 'int',
	];
}
