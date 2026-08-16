<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $primaryKey = 'bookmark_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'internship_id',
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
