<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectApplicationRequest;
use App\Models\Application;
use App\Models\Employer;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmployerApplicationController extends Controller
{
    private function ownedCompany(Request $request)
    {
        $employer = Employer::where('user_id', $request->user()->user_id)->first();

        return $employer ? $employer->company : null;
    }

    public function index(Request $request, Internship $internship)
    {
        $company = $this->ownedCompany($request);

        if (! $company || $internship->company_id !== $company->company_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to view these applications.'], 403);
        }

        $query = $internship->applications()->with(['student.user', 'interviews'])->latest('application_id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function show(Request $request, Application $application)
    {
        $company = $this->ownedCompany($request);

        if (! $company || $application->internship->company_id !== $company->company_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to view this application.'], 403);
        }

        return response()->json(['success' => true, 'data' => $application->load('student.user')]);
    }

    public function accept(Request $request, Application $application)
    {
        $company = $this->ownedCompany($request);

        if (! $company || $application->internship->company_id !== $company->company_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to review this application.'], 403);
        }

        if ($application->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Only pending applications can be accepted.'], 422);
        }

        $application->update(['status' => 'Accepted', 'reviewed_at' => now()]);

        $studentEmail = $application->student?->user?->email;
        if ($studentEmail) {
            // company_email is optional, user-entered, and can be stale — the employer's login email is more reliable.
            $contactEmail = $company->employer?->user?->email ?? $company->company_email;

            $body = "Congratulations! Your application for \"{$application->internship->title}\" at {$company->company_name} has been accepted.";
            $body .= $contactEmail
                ? "\n\nIf you have any questions or need to get in touch, you can reach {$company->company_name} at {$contactEmail}."
                : "\n\nYou can follow up with them through InternHub.";

            $this->sendMail($studentEmail, 'Application Accepted — InternHub', $body);
        }

        return response()->json(['success' => true, 'message' => 'Application accepted.']);
    }

    public function reject(RejectApplicationRequest $request, Application $application)
    {
        $company = $this->ownedCompany($request);

        if (! $company || $application->internship->company_id !== $company->company_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to review this application.'], 403);
        }

        if ($application->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Only pending applications can be rejected.'], 422);
        }

        $feedback = $request->validated()['feedback'] ?? null;

        $application->update([
            'status' => 'Rejected',
            'reviewed_at' => now(),
            'employer_feedback' => $feedback,
        ]);

        $studentEmail = $application->student?->user?->email;
        if ($studentEmail) {
            $body = "Your application for \"{$application->internship->title}\" was not successful this time.";
            if ($feedback) {
                $body .= "\n\nFeedback from the employer: {$feedback}";
            }
            $this->sendMail($studentEmail, 'Application Update — InternHub', $body);
        }

        return response()->json(['success' => true, 'message' => 'Application rejected.']);
    }

    /** Best-effort email — a delivery failure must never break the request that triggered it. */
    private function sendMail(string $to, string $subject, string $body): void
    {
        try {
            Mail::raw($body, fn ($message) => $message->to($to)->subject($subject));
        } catch (\Throwable $e) {
            Log::warning('Failed to send application email', ['to' => $to, 'error' => $e->getMessage()]);
        }
    }
}
