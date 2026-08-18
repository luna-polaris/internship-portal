<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStudentProfileRequest;
use App\Models\Recommendation;
use App\Models\Student;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(private RecommendationService $recommendations) {}

    public function show(Request $request)
    {
        $profile = Student::with('user')->where('user_id', $request->user()->user_id)->first();

        if (! $profile) {
            return response()->json(['success' => false, 'message' => 'Student profile not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $profile]);
    }

    public function update(UpdateStudentProfileRequest $request)
    {
        $student = Student::where('user_id', $request->user()->user_id)->first();

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found.'], 404);
        }

        $student->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    public function uploadResume(Request $request)
    {
        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $student = Student::where('user_id', $request->user()->user_id)->first();

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found.'], 404);
        }

        $path = $request->file('resume')->store('resumes', 'public');
        $student->update(['resume' => $path]);

        return response()->json(['success' => true, 'message' => 'Resume uploaded.', 'path' => $path]);
    }

    public function index()
    {
        $students = Student::with('user')->get();

        return response()->json(['success' => true, 'data' => $students]);
    }

    // Computed lazily on first access; later refreshes come from StudentObserver/InternshipObserver, not here.
    public function recommended(Request $request)
    {
        $student = Student::where('user_id', $request->user()->user_id)->first();

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found.'], 404);
        }

        if (! Recommendation::where('student_id', $student->student_id)->exists()) {
            $this->recommendations->refreshForStudent($student);
        }

        $recommended = Recommendation::where('student_id', $student->student_id)
            ->with('internship.company:company_id,company_name,city,state,logo')
            ->orderByDesc('score')
            ->limit(10)
            ->get()
            ->pluck('internship')
            ->filter();

        return response()->json(['success' => true, 'data' => $recommended->values()]);
    }
}
