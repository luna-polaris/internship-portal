<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Internship;
use App\Models\Student;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    private function ownedStudent(Request $request)
    {
        return Student::where('user_id', $request->user()->user_id)->first();
    }

    public function toggle(Request $request, Internship $internship)
    {
        $student = $this->ownedStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found.'], 404);
        }

        $bookmark = Bookmark::where('student_id', $student->student_id)
            ->where('internship_id', $internship->internship_id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();

            return response()->json(['success' => true, 'message' => 'Bookmark removed.', 'bookmarked' => false]);
        }

        if (! Internship::visible()->whereKey($internship->internship_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Internship posting not found.'], 404);
        }

        Bookmark::create([
            'student_id' => $student->student_id,
            'internship_id' => $internship->internship_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Internship bookmarked.', 'bookmarked' => true]);
    }

    public function index(Request $request)
    {
        $student = $this->ownedStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found.'], 404);
        }

        $bookmarks = $student->bookmarks()->with('internship.company')->latest('bookmark_id')->get();

        return response()->json(['success' => true, 'data' => $bookmarks]);
    }
}
