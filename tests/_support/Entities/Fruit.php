<?php

namespace Tests\Support\Entities;

use Tatter\Firebase\Firestore\Entity;

final class Fruit extends Entity
{
    protected $casts = [
        'weight' => 'int',
        'zero'   => 'int',
    ];
    protected $attributes = [
        'zero' => 0,
    ];
}
