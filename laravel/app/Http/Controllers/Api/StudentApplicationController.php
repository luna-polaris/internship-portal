<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyInternshipRequest;
use App\Models\Application;
use App\Models\Internship;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class StudentApplicationController extends Controller
{
    private function ownedStudent(Request $request)
    {
        return Student::where('user_id', $request->user()->user_id)->first();
    }

    public function apply(ApplyInternshipRequest $request, Internship $internship)
    {
        $student = $this->ownedStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found.'], 404);
        }

        if (! Internship::visible()->whereKey($internship->internship_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Internship posting not found.'], 404);
        }

        if (empty($student->resume)) {
            return response()->json(['success' => false, 'message' => 'Please upload your resume before applying.'], 422);
        }

        if (Application::where('student_id', $student->student_id)->where('internship_id', $internship->internship_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'You have already applied to this internship.'], 422);
        }

        $application = Application::create($request->validated() + [
            'student_id' => $student->student_id,
            'internship_id' => $internship->internship_id,
            'status' => 'Pending',
        ]);

        $employerEmail = $internship->company?->employer?->user?->email;
        if ($employerEmail) {
            Mail::raw(
                "You have a new application for \"{$internship->title}\" from {$student->user->full_name}. Log in to InternHub to review it.",
                fn ($message) => $message->to($employerEmail)->subject('New Application — InternHub')
            );
        }

        return response()->json(['success' => true, 'message' => 'Application submitted.', 'data' => $application], 201);
    }

    public function index(Request $request)
    {
        $student = $this->ownedStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found.'], 404);
        }

        $query = $student->applications()->with(['internship.company', 'interviews'])->latest('application_id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function show(Request $request, Application $application)
    {
        $student = $this->ownedStudent($request);

        if (! $student || $application->student_id !== $student->student_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to view this application.'], 403);
        }

        return response()->json(['success' => true, 'data' => $application->load('internship.company')]);
    }

    public function withdraw(Request $request, Application $application)
    {
        $student = $this->ownedStudent($request);

        if (! $student || $application->student_id !== $student->student_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to withdraw this application.'], 403);
        }

        if ($application->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Only pending applications can be withdrawn.'], 422);
        }

        $application->update(['status' => 'Withdrawn']);

        return response()->json(['success' => true, 'message' => 'Application withdrawn.']);
    }
}
