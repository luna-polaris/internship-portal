<?php

namespace App\Factories;

use Illuminate\Database\Eloquent\Model;

/**
 * Factory Method pattern: each concrete factory knows how to build the
 * role-specific profile model that accompanies a User (Student, Employer,
 * Admin). Kept under App\Factories rather than database/factories to avoid
 * colliding with Laravel's unrelated "model factory" (seeding/testing) concept.
 */
interface ProfileFactory
{
    public function createProfile(): Model;
}
