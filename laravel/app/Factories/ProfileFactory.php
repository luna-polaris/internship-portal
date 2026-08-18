<?php

namespace App\Factories;

use Illuminate\Database\Eloquent\Model;

/** Kept under App\Factories, not database/factories, to avoid colliding with Laravel's unrelated model-factory (seeding/testing) concept. */
interface ProfileFactory
{
    public function createProfile(): Model;
}
