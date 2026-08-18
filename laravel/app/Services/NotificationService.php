<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Collection;

/** Single place that creates in-app alerts, keeping wording and link formats consistent across the app. */
class NotificationService
{
    public function notify(int $userId, string $type, string $title, string $message, ?string $link = null): UserNotification
    {
        return UserNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
        ]);
    }

    /** Fan out one alert to every user holding a role (used to reach all Admins). */
    public function notifyRole(string $role, string $type, string $title, string $message, ?string $link = null): Collection
    {
        return User::where('role', $role)
            ->where('status', 'Active')
            ->pluck('user_id')
            ->map(fn ($id) => $this->notify($id, $type, $title, $message, $link));
    }

    public function evaluationSubmitted(int $evaluationId, string $studentName, string $companyName): void
    {
        $this->notifyRole(
            'Admin',
            'evaluation.submitted',
            'Evaluation waiting for review',
            "{$companyName} submitted a performance evaluation for {$studentName}.",
            "/admin/evaluations/{$evaluationId}"
        );
    }

    public function evaluationApproved(int $studentUserId, int $evaluationId, string $companyName): void
    {
        $this->notify(
            $studentUserId,
            'evaluation.approved',
            'Your evaluation is ready',
            "{$companyName} has evaluated your performance. Open it to see your scores and feedback.",
            "/evaluations/{$evaluationId}"
        );
    }

    public function evaluationRejected(int $employerUserId, int $evaluationId, string $remark): void
    {
        $this->notify(
            $employerUserId,
            'evaluation.rejected',
            'Evaluation sent back for changes',
            "An admin returned your evaluation: {$remark}",
            '/evaluations/create'
        );
    }

    public function internshipMatched(int $studentUserId, int $internshipId, string $title, string $companyName): void
    {
        $this->notify(
            $studentUserId,
            'internship.recommended',
            'New internship matches your profile',
            "{$companyName} posted \"{$title}\", which matches your skills and interests.",
            "/internships/{$internshipId}"
        );
    }

    public function interviewScheduled(int $studentUserId, int $interviewId, string $companyName, string $whenText): void
    {
        $this->notify(
            $studentUserId,
            'interview.scheduled',
            'Interview scheduled',
            "{$companyName} scheduled an interview with you for {$whenText}.",
            "/student/applications"
        );
    }

    public function interviewRescheduled(int $studentUserId, int $interviewId, string $companyName, string $whenText): void
    {
        $this->notify(
            $studentUserId,
            'interview.rescheduled',
            'Interview rescheduled',
            "{$companyName} moved your interview to {$whenText}.",
            "/student/applications"
        );
    }

    public function interviewCancelled(int $studentUserId, int $interviewId, string $companyName, ?string $reason): void
    {
        $this->notify(
            $studentUserId,
            'interview.cancelled',
            'Interview cancelled',
            $reason
                ? "{$companyName} cancelled your interview: {$reason}"
                : "{$companyName} cancelled your interview.",
            "/student/applications"
        );
    }

    public function interviewCompleted(int $studentUserId, int $interviewId, string $companyName): void
    {
        $this->notify(
            $studentUserId,
            'interview.completed',
            'Interview completed',
            "Your interview with {$companyName} has been marked as completed.",
            "/student/applications"
        );
    }

    public function interviewRescheduleRequested(int $employerUserId, int $interviewId, string $studentName, string $whenText): void
    {
        $this->notify(
            $employerUserId,
            'interview.reschedule_requested',
            'Reschedule request',
            "{$studentName} requested to move their interview to {$whenText}. Review and confirm.",
            "/employer/internships"
        );
    }

    public function interviewRescheduleApproved(int $studentUserId, int $interviewId, string $companyName, string $whenText): void
    {
        $this->notify(
            $studentUserId,
            'interview.reschedule_approved',
            'Reschedule confirmed',
            "{$companyName} confirmed your requested time — your interview is now {$whenText}.",
            "/student/applications"
        );
    }

    public function interviewRescheduleDeclined(int $studentUserId, int $interviewId, string $companyName, ?string $reason): void
    {
        $this->notify(
            $studentUserId,
            'interview.reschedule_declined',
            'Reschedule request declined',
            $reason
                ? "{$companyName} could not accommodate your requested time: {$reason}. Your original interview time still stands."
                : "{$companyName} could not accommodate your requested time. Your original interview time still stands.",
            "/student/applications"
        );
    }

    public function criteriaChanged(string $criterionName): void
    {
        $this->notifyRole(
            'Employer',
            'criteria.updated',
            'Evaluation rubric updated',
            "The criterion \"{$criterionName}\" changed. New evaluations use the updated rubric.",
            '/evaluations'
        );
    }
}