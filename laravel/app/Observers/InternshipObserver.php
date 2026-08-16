<?php

namespace App\Observers;

use App\Models\Internship;
use App\Services\NotificationService;
use App\Services\RecommendationService;

/**
 * Module 3 — Observer side #1: when a posting goes live, matching students
 * are found via the recommendation engine and notified automatically,
 * instead of the employer or an admin having to trigger it manually.
 */
class InternshipObserver
{
    public function __construct(
        private RecommendationService $recommendations,
        private NotificationService $notifications,
    ) {}

    public function created(Internship $internship): void
    {
        if ($internship->status === 'Published') {
            $this->matchAndNotify($internship);
        }
    }

    public function updated(Internship $internship): void
    {
        if ($internship->wasChanged('status') && $internship->status === 'Published') {
            $this->matchAndNotify($internship);
        }
    }

    private function matchAndNotify(Internship $internship): void
    {
        $matches = $this->recommendations->refreshForInternship($internship);

        $companyName = $internship->company?->company_name ?? 'An employer';

        foreach ($matches as $recommendation) {
            $userId = $recommendation->student?->user_id;

            if ($userId) {
                $this->notifications->internshipMatched($userId, $internship->internship_id, $internship->title, $companyName);
            }
        }
    }
}
