<?php namespace Tests\Support\Entities;

use Tatter\Firebase\Entity;

class WithCollections extends Entity
{
	protected $casts = [
		'age'    => 'int',
		'weight' => 'int',
	];

	/**
	 * Array of subcollections supported by this entity. Actual
	 * references may or may not exist with documents. Keys are the
	 * name of the collection and the values are the model to use
	 * when handling them, or null for raw Firestore.
	 *
	 * @var array of name => model|null
	 */
	protected $collections = ['colors' => 'Tests\Support\Models\ColorModel'];
}
