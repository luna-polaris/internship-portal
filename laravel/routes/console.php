<?php

use App\Models\Internship;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('internships:close-expired', function () {
    $count = Internship::where('status', 'Published')
        ->whereNotNull('application_deadline')
        ->where('application_deadline', '<', now()->toDateString())
        ->update(['status' => 'Closed']);

    $this->info("Closed {$count} expired internship posting(s).");
})->purpose('Close Published internships whose application deadline has passed');

Schedule::command('internships:close-expired')->daily();
