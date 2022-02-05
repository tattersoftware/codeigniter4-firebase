<?php

namespace Tests\Support\Collections;

use Tatter\Firebase\Firestore\Collection;
use Tests\Support\Entities\Fruit;

final class FruitCollection extends Collection
{
    public const NAME   = 'fruits';
    public const ENTITY = Fruit::class;

    protected $allowedFields = [
        'name',
        'taste',
        'weight',
        'zero',
    ];
    protected $validationRules = [
        'name'   => 'required|string',
        'taste'  => 'permit_empty|string',
        'weight' => 'is_natural_no_zero',
        'zero'   => 'numeric|in_list[0]',
    ];
    protected $validationMessages = [
        'zero' => 'The zero field should be supplied by attributes.',
    ];

    public function fake(array $overrides = []): Fruit
    {
        return new Fruit(array_merge([
            'name'   => 'name',
            'taste'  => 'taste',
            'weight' => 1,
        ], $overrides));
    }
}
