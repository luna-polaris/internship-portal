<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $primaryKey = 'recommendation_id';

    protected $fillable = [
        'student_id',
        'internship_id',
        'score',
        'matched_reasons',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'matched_reasons' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'internship_id');
    }
}
