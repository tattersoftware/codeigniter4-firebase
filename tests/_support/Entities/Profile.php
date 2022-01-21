<?php

namespace Tests\Support\Entities;

use Tatter\Firebase\Entity;

class Profile extends Entity
{
    protected $casts = [
        'age'    => 'int',
        'weight' => 'int',
    ];
}
