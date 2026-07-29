<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    protected $primaryKey = 'employer_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'position',
        'department',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'employer_id', 'employer_id');
    }
}
