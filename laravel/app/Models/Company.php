<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $primaryKey = 'company_id';
    public $timestamps = false;

    protected $fillable = [
        'employer_id',
        'company_name',
        'registration_no',
        'industry',
        'address',
        'city',
        'state',
        'postcode',
        'website',
        'company_email',
        'company_phone',
        'description',
        'logo',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employer_id', 'employer_id');
    }

    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('company_name', 'like', "%{$keyword}%")
                ->orWhere('industry', 'like', "%{$keyword}%")
                ->orWhere('city', 'like', "%{$keyword}%");
        });
    }
}
