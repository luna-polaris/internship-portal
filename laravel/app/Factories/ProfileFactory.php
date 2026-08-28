<?php

namespace App\Factories;

use Illuminate\Database\Eloquent\Model;

/** Kept under App\Factories, not database/factories */
interface ProfileFactory
{
    public function createProfile(): Model;
}
