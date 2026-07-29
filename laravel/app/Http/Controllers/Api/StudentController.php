<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStudentProfileRequest;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
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
}
