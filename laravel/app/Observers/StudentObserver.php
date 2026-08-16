<?php

namespace App\Observers;

use App\Models\Student;
use App\Services\RecommendationService;

/**
 * Module 3 — Observer side #2: when a student's profile changes in a way
 * that affects matching (skills, interests, preferred locations, CGPA),
 * their stored recommendations are refreshed automatically.
 */
class StudentObserver
{
    public function __construct(private RecommendationService $recommendations) {}

    public function created(Student $student): void
    {
        $this->recommendations->refreshForStudent($student);
    }

    public function updated(Student $student): void
    {
        if ($student->wasChanged(['skills', 'interests', 'preferred_locations', 'cgpa'])) {
            $this->recommendations->refreshForStudent($student);
        }
    }
}
