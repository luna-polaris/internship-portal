<?php

namespace App\Models;

use App\Support\ListSanitizer;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $primaryKey = 'student_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'matric_no',
        'university',
        'faculty',
        'programme',
        'cgpa',
        'graduation_year',
        'resume',
        'skills',
        'interests',
        'preferred_locations',
    ];

    protected $casts = [
        'skills' => 'array',
        'interests' => 'array',
        'preferred_locations' => 'array',
        'cgpa' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'student_id', 'student_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'student_id', 'student_id');
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class, 'student_id', 'student_id');
    }

    /** Strip tags, trim, dedupe, and cap free-text list fields before they ever reach storage. */
    public function setSkillsAttribute($value): void
    {
        $this->attributes['skills'] = ListSanitizer::toJson($value);
    }

    public function setInterestsAttribute($value): void
    {
        $this->attributes['interests'] = ListSanitizer::toJson($value);
    }

    public function setPreferredLocationsAttribute($value): void
    {
        $this->attributes['preferred_locations'] = ListSanitizer::toJson($value, 10, 100);
    }
}
