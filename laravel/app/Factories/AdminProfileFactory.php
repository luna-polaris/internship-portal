<?php

namespace App\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;

class AdminProfileFactory implements ProfileFactory
{
    public function createProfile(): Model
    {
        return new Admin();
    }
}
