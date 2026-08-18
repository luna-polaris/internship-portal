<?php

namespace App\Observers;

use App\Models\Student;
use App\Services\RecommendationService;

/** Refreshes stored recommendations automatically when a profile change could affect matching. */
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
