<?php

namespace App\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;

class StudentProfileFactory implements ProfileFactory
{
    public function createProfile(): Model
    {
        return new Student();
    }
}
