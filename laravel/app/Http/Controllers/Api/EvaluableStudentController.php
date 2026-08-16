<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Students this employer is allowed to evaluate — i.e. anyone accepted onto
 * one of their own company's internships. Backs the intern dropdown on the
 * evaluation form; the same rule is enforced again in EvaluationController.
 */
class EvaluableStudentController extends Controller
{
    /** Application statuses that mean the student actually joined. */
    private const ACCEPTED_STATUSES = ['Accepted'];

    public function index(Request $request): JsonResponse
    {
        $employer = $request->user()->employer;
        abort_unless($employer, 403, 'Complete your employer profile first.');

        $rows = DB::table('applications as a')
            ->join('internships as i', 'i.internship_id', '=', 'a.internship_id')
            ->join('companies as c', 'c.company_id', '=', 'i.company_id')
            ->join('students as s', 's.student_id', '=', 'a.student_id')
            ->join('users as u', 'u.user_id', '=', 's.user_id')
            ->where('c.employer_id', $employer->employer_id)
            ->whereIn('a.status', self::ACCEPTED_STATUSES)
            ->select(
                's.student_id',
                's.matric_no',
                'u.full_name',
                'i.title as internship_title',
                'a.application_id'
            )
            ->orderBy('u.full_name')
            ->get();

        return response()->json(['data' => $rows]);
    }
}   