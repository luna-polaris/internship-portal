<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $primaryKey = 'application_id';

    protected $fillable = [
        'student_id',
        'internship_id',
        'cover_letter',
        'status',
        'employer_feedback',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'internship_id');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'application_id', 'application_id');
    }
}
