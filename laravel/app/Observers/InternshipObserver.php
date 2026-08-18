<?php

namespace App\Observers;

use App\Models\Internship;
use App\Services\NotificationService;
use App\Services\RecommendationService;

/** Notifies matching students automatically when a posting goes live, instead of requiring a manual trigger. */
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
