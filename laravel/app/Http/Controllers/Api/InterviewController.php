<?php

namespace App\Http\Controllers\Api;

use App\Commands\Interview\ApproveRescheduleRequestCommand;
use App\Commands\Interview\CancelInterviewCommand;
use App\Commands\Interview\CompleteInterviewCommand;
use App\Commands\Interview\DeclineRescheduleRequestCommand;
use App\Commands\Interview\RequestRescheduleCommand;
use App\Commands\Interview\RescheduleInterviewCommand;
use App\Commands\Interview\ScheduleInterviewCommand;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelInterviewRequest;
use App\Http\Requests\DeclineRescheduleRequest;
use App\Http\Requests\RequestRescheduleRequest;
use App\Http\Requests\RescheduleInterviewRequest;
use App\Http\Requests\ScheduleInterviewRequest;
use App\Models\Application;
use App\Models\Interview;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Module 4 — Interview Scheduling & Notification.
 *
 * Visibility/authorization, in one place:
 *   Employer — schedule/manage interviews for their own company's applications
 *   Admin    — schedule/manage interviews for any application
 *   Student  — read-only, only their own interviews
 */
class InterviewController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Interview::query()
            ->with(['application.student.user:user_id,full_name', 'application.internship.company:company_id,employer_id,company_name'])
            ->latest('scheduled_at');

        match ($user->role) {
            'Student' => $query->whereHas('application.student', fn ($q) => $q->where('user_id', $user->user_id)),
            'Employer' => $query->whereHas('application.internship.company.employer', fn ($q) => $q->where('user_id', $user->user_id)),
            'Admin' => null,
            default => abort(403),
        };

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json(['success' => true, 'data' => $query->paginate(15)]);
    }

    public function show(Request $request, Interview $interview): JsonResponse
    {
        $this->authorizeView($request, $interview);

        $interview->load([
            'application.student.user:user_id,full_name,email',
            'application.internship.company:company_id,employer_id,company_name',
            'logs.performedBy:user_id,full_name',
        ]);

        return response()->json(['success' => true, 'data' => $interview]);
    }

    /** Function 1 — Schedule Interview. */
    public function store(ScheduleInterviewRequest $request, Application $application): JsonResponse
    {
        $user = $request->user();
        $this->authorizeManage($user, $application);

        if (! in_array($application->status, ['Pending', 'Accepted'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Interviews can only be scheduled for pending or accepted applications.',
            ], 422);
        }

        $interview = (new ScheduleInterviewCommand($application, $request->validated(), $user->user_id))->execute();

        $this->notifyStudent($interview, function ($companyName, $whenText, $studentEmail) use ($interview) {
            $this->notifications->interviewScheduled($interview->application->student->user_id, $interview->interview_id, $companyName, $whenText);

            $this->emailUser(
                $studentEmail,
                'Interview Scheduled — InternHub',
                "{$companyName} has scheduled an interview with you.\n\n"
                . "When: {$whenText}\n"
                . "Mode: {$interview->mode}\n"
                . ($interview->mode === 'Online' ? "Meeting link: {$interview->meeting_link}\n" : "Venue: {$interview->location}\n")
                . ($interview->remarks ? "\nNote from {$companyName}: {$interview->remarks}" : '')
            );
        });

        return response()->json(['success' => true, 'message' => 'Interview scheduled.', 'data' => $interview], 201);
    }

    /** Function 3 — Reschedule Interview. */
    public function reschedule(RescheduleInterviewRequest $request, Interview $interview): JsonResponse
    {
        $user = $request->user();
        $this->authorizeManage($user, $interview->application);
        $this->guardEditable($interview);

        $interview = (new RescheduleInterviewCommand($interview, $request->validated(), $user->user_id))->execute();

        $this->notifyStudent($interview, function ($companyName, $whenText, $studentEmail) use ($interview) {
            $this->notifications->interviewRescheduled($interview->application->student->user_id, $interview->interview_id, $companyName, $whenText);

            $this->emailUser(
                $studentEmail,
                'Interview Rescheduled — InternHub',
                "{$companyName} moved your interview to a new time.\n\n"
                . "New time: {$whenText}\n"
                . "Mode: {$interview->mode}\n"
                . ($interview->mode === 'Online' ? "Meeting link: {$interview->meeting_link}\n" : "Venue: {$interview->location}\n")
            );
        });

        return response()->json(['success' => true, 'message' => 'Interview rescheduled.', 'data' => $interview]);
    }

    /** Student proposes a new time — doesn't change the schedule until an employer/admin approves it. */
    public function requestReschedule(RequestRescheduleRequest $request, Interview $interview): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStudentOwn($user, $interview);
        $this->guardEditable($interview);

        $interview = (new RequestRescheduleCommand($interview, $request->validated(), $user->user_id))->execute();

        $interview->loadMissing(['application.student.user', 'application.internship.company.employer.user']);
        $employer = $interview->application->internship->company?->employer;
        $studentName = $interview->application->student->user->full_name;
        $whenText = $interview->requested_scheduled_at->format('D, d M Y \a\t g:i A');

        if ($employer) {
            $this->notifications->interviewRescheduleRequested($employer->user_id, $interview->interview_id, $studentName, $whenText);

            $this->emailUser(
                $employer->user?->email,
                'Interview Reschedule Request — InternHub',
                "{$studentName} requested to move their interview to {$whenText}."
                . ($interview->requested_reason ? "\n\nReason: {$interview->requested_reason}" : '')
                . "\n\nLog in to InternHub to approve or decline this request."
            );
        }

        return response()->json(['success' => true, 'message' => 'Reschedule request sent to the employer.', 'data' => $interview]);
    }

    /** Employer/Admin confirms the student's proposed time. */
    public function approveReschedule(Request $request, Interview $interview): JsonResponse
    {
        $user = $request->user();
        $this->authorizeManage($user, $interview->application);
        $this->guardPendingRequest($interview);

        $interview = (new ApproveRescheduleRequestCommand($interview, $user->user_id))->execute();

        $this->notifyStudent($interview, function ($companyName, $whenText, $studentEmail) use ($interview) {
            $this->notifications->interviewRescheduleApproved($interview->application->student->user_id, $interview->interview_id, $companyName, $whenText);

            $this->emailUser(
                $studentEmail,
                'Interview Reschedule Confirmed — InternHub',
                "{$companyName} confirmed your requested time.\n\n"
                . "New time: {$whenText}\n"
                . "Mode: {$interview->mode}\n"
                . ($interview->mode === 'Online' ? "Meeting link: {$interview->meeting_link}\n" : "Venue: {$interview->location}\n")
            );
        });

        return response()->json(['success' => true, 'message' => 'Reschedule request approved.', 'data' => $interview]);
    }

    /** Employer/Admin declines the student's proposed time — the original schedule stands. */
    public function declineReschedule(DeclineRescheduleRequest $request, Interview $interview): JsonResponse
    {
        $user = $request->user();
        $this->authorizeManage($user, $interview->application);
        $this->guardPendingRequest($interview);

        $reason = $request->validated()['reason'] ?? null;
        $interview = (new DeclineRescheduleRequestCommand($interview, $reason, $user->user_id))->execute();

        $interview->loadMissing(['application.student.user', 'application.internship.company']);
        $companyName = $interview->application->internship->company?->company_name ?? 'The employer';
        $studentEmail = $interview->application->student->user->email ?? null;

        $this->notifications->interviewRescheduleDeclined($interview->application->student->user_id, $interview->interview_id, $companyName, $reason);

        $this->emailUser(
            $studentEmail,
            'Interview Reschedule Request Declined — InternHub',
            $reason
                ? "{$companyName} could not accommodate your requested time: {$reason}\n\nYour original interview time still stands."
                : "{$companyName} could not accommodate your requested time.\n\nYour original interview time still stands."
        );

        return response()->json(['success' => true, 'message' => 'Reschedule request declined.', 'data' => $interview]);
    }

    /** Function 3 — Cancel Interview. */
    public function cancel(CancelInterviewRequest $request, Interview $interview): JsonResponse
    {
        $user = $request->user();
        $this->authorizeManage($user, $interview->application);
        $this->guardEditable($interview);

        $reason = $request->validated()['reason'] ?? null;
        $interview = (new CancelInterviewCommand($interview, $reason, $user->user_id))->execute();

        $companyName = $interview->application->internship->company?->company_name ?? 'The employer';
        $studentEmail = $interview->application->student->user->email ?? null;

        $this->notifications->interviewCancelled($interview->application->student->user_id, $interview->interview_id, $companyName, $reason);

        $this->emailUser(
            $studentEmail,
            'Interview Cancelled — InternHub',
            $reason
                ? "{$companyName} cancelled your interview.\n\nReason: {$reason}"
                : "{$companyName} cancelled your interview."
        );

        return response()->json(['success' => true, 'message' => 'Interview cancelled.', 'data' => $interview]);
    }

    /** Function 4 — Track Interview Status: mark an interview as completed. */
    public function complete(Request $request, Interview $interview): JsonResponse
    {
        $user = $request->user();
        $this->authorizeManage($user, $interview->application);

        if (! in_array($interview->status, [Interview::STATUS_SCHEDULED, Interview::STATUS_RESCHEDULED], true)) {
            return response()->json(['success' => false, 'message' => 'Only a scheduled interview can be marked completed.'], 422);
        }

        $interview = (new CompleteInterviewCommand($interview, $user->user_id))->execute();

        $interview->loadMissing(['application.student.user', 'application.internship.company.employer.user']);
        $company = $interview->application->internship->company;
        $companyName = $company?->company_name ?? 'The employer';
        $studentEmail = $interview->application->student->user->email ?? null;
        // The employer's own login email is always correct; company_email is an
        // optional, user-entered field that can be blank, stale, or wrong.
        $contactEmail = $company?->employer?->user?->email ?? $company?->company_email;

        $this->notifications->interviewCompleted($interview->application->student->user_id, $interview->interview_id, $companyName);

        $this->emailUser(
            $studentEmail,
            'Interview Completed — InternHub',
            "Your interview with {$companyName} has been marked as completed.\n\n"
            . ($contactEmail
                ? "If you'd like to follow up or have any questions, you can reach them at {$contactEmail}."
                : "You can follow up with them through InternHub.")
        );

        return response()->json(['success' => true, 'message' => 'Interview marked as completed.', 'data' => $interview]);
    }

    private function guardEditable(Interview $interview): void
    {
        abort_if(
            in_array($interview->status, [Interview::STATUS_COMPLETED, Interview::STATUS_CANCELLED], true),
            422,
            "This interview is already {$interview->status} and can no longer be changed."
        );
    }

    private function guardPendingRequest(Interview $interview): void
    {
        abort_unless($interview->hasPendingRescheduleRequest(), 422, 'There is no pending reschedule request on this interview.');
    }

    /** Only the student who owns the application may request a reschedule. */
    private function authorizeStudentOwn($user, Interview $interview): void
    {
        abort_unless(
            $user->role === 'Student' && $interview->application?->student?->user_id === $user->user_id,
            403,
            'You are not authorized to request a reschedule for this interview.'
        );
    }

    private function notifyStudent(Interview $interview, callable $send): void
    {
        $interview->loadMissing(['application.student.user', 'application.internship.company']);
        $companyName = $interview->application->internship->company?->company_name ?? 'The employer';
        $whenText = $interview->scheduled_at->format('D, d M Y \a\t g:i A');
        $studentEmail = $interview->application->student->user->email ?? null;

        $send($companyName, $whenText, $studentEmail);
    }

    /** Best-effort email — a delivery failure must never break the request that triggered it. */
    private function emailUser(?string $to, string $subject, string $body): void
    {
        if (! $to) {
            return;
        }

        try {
            Mail::raw($body, fn ($message) => $message->to($to)->subject($subject));
        } catch (\Throwable $e) {
            Log::warning('Failed to send interview notification email', ['to' => $to, 'error' => $e->getMessage()]);
        }
    }

    /** Employer must own the company behind the application; Admin can manage any. */
    private function authorizeManage($user, Application $application): void
    {
        if ($user->role === 'Admin') {
            return;
        }

        abort_unless(
            $user->role === 'Employer'
                && $application->internship?->company?->employer?->user_id === $user->user_id,
            403,
            'You are not authorized to manage interviews for this application.'
        );
    }

    private function authorizeView(Request $request, Interview $interview): void
    {
        $user = $request->user();

        $allowed = match ($user->role) {
            'Admin' => true,
            'Employer' => $interview->application->internship?->company?->employer?->user_id === $user->user_id,
            'Student' => $interview->application->student?->user_id === $user->user_id,
            default => false,
        };

        abort_unless($allowed, 403, 'You do not have access to this interview.');
    }
}
