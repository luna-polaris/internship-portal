<?php

namespace App\Factories;

use App\Models\Employer;
use Illuminate\Database\Eloquent\Model;

class EmployerProfileFactory implements ProfileFactory
{
    public function createProfile(): Model
    {
        return new Employer();
    }
}
