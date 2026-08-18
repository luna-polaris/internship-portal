<?php

namespace App\Providers;

use App\Models\Internship;
use App\Models\Student;
use App\Observers\InternshipObserver;
use App\Observers\StudentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Student::observe(StudentObserver::class);
        Internship::observe(InternshipObserver::class);
    }
}
