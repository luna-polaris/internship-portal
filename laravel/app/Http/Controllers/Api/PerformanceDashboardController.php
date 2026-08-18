<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationCriterion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// One endpoint, three shapes: each role gets the numbers it can actually act on.
class PerformanceDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'role' => $request->user()->role,
            'data' => match ($request->user()->role) {
                'Student'  => $this->forStudent($request),
                'Employer' => $this->forEmployer($request),
                'Admin'    => $this->forAdmin(),
                default    => abort(403),
            },
        ]);
    }

    /** A student sees their own approved results and how they moved between periods. */
    private function forStudent(Request $request): array
    {
        $studentId = $request->user()->student?->student_id;
        abort_unless($studentId, 404, 'No student profile found for this account.');

        $evaluations = Evaluation::visibleToStudent()
            ->where('student_id', $studentId)
            ->with(['scores.criterion', 'employer.company:company_id,employer_id,company_name'])
            ->orderBy('submitted_at')
            ->get();

        return [
            'evaluations_received' => $evaluations->count(),
            'latest_total_score'   => $evaluations->last()?->total_score,
            'average_total_score'  => round((float) $evaluations->avg('total_score'), 2),
            'by_criterion'         => $this->criterionBreakdown($evaluations),
            'trend'                => $evaluations->map(fn ($e) => [
                'period'      => $e->period,
                'company'     => $e->employer->company?->company_name,
                'total_score' => $e->total_score,
                'received_at' => $e->submitted_at?->toDateString(),
            ])->values(),
            'latest_feedback'      => $evaluations->last()?->only(['strengths', 'improvements', 'overall_comment']),
        ];
    }

    /** An employer sees their own submission pipeline and how their interns compare. */
    private function forEmployer(Request $request): array
    {
        $employerId = $request->user()->employer?->employer_id;
        abort_unless($employerId, 404, 'No employer profile found for this account.');

        $evaluations = Evaluation::where('employer_id', $employerId)
            ->with(['scores.criterion', 'student.user:user_id,full_name'])
            ->get();

        return [
            'total_written'       => $evaluations->count(),
            'by_status'           => $evaluations->countBy('status'),
            'awaiting_review'     => $evaluations->where('status', Evaluation::STATUS_SUBMITTED)->count(),
            'needs_your_action'   => $evaluations->whereIn('status', [Evaluation::STATUS_DRAFT, Evaluation::STATUS_REJECTED])->count(),
            'average_total_score' => round((float) $evaluations->whereNotNull('total_score')->avg('total_score'), 2),
            'by_criterion'        => $this->criterionBreakdown($evaluations),
            'interns'             => $evaluations->map(fn ($e) => [
                'evaluation_id' => $e->evaluation_id,
                'student'       => $e->student->user->full_name ?? null,
                'period'        => $e->period,
                'total_score'   => $e->total_score,
                'status'        => $e->status,
            ])->sortByDesc('total_score')->values(),
        ];
    }

    /** An admin sees the review queue plus programme-wide averages. */
    private function forAdmin(): array
    {
        $statusCounts = Evaluation::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $criterionAverages = DB::table('evaluation_scores as es')
            ->join('evaluations as e', 'e.evaluation_id', '=', 'es.evaluation_id')
            ->join('evaluation_criteria as c', 'c.criterion_id', '=', 'es.criterion_id')
            ->where('e.status', Evaluation::STATUS_APPROVED)
            ->groupBy('c.criterion_id', 'c.name', 'c.max_score')
            ->select(
                'c.name',
                'c.max_score',
                DB::raw('ROUND(AVG(es.score), 2) as average_score'),
                DB::raw('COUNT(*) as responses')
            )
            ->orderBy('average_score')
            ->get();

        $monthly = Evaluation::where('status', Evaluation::STATUS_APPROVED)
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(DB::raw("DATE_FORMAT(submitted_at, '%Y-%m') as month"), DB::raw('COUNT(*) as total'))
            ->groupBy('month')->orderBy('month')->get();

        return [
            'approved_total'      => (int) ($statusCounts[Evaluation::STATUS_APPROVED] ?? 0),
            'returned_total'      => (int) ($statusCounts[Evaluation::STATUS_REJECTED] ?? 0),
            'by_status'           => $statusCounts,
            'pending_review'      => (int) ($statusCounts[Evaluation::STATUS_SUBMITTED] ?? 0),
            'average_total_score' => round((float) Evaluation::where('status', Evaluation::STATUS_APPROVED)->avg('total_score'), 2),
            'by_criterion'        => $criterionAverages,
            'weakest_criterion'   => $criterionAverages->first(),
            'submissions_by_month'=> $monthly,
            'rubric_health'       => [
                'active_criteria'     => EvaluationCriterion::active()->count(),
                'active_weight_total' => EvaluationCriterion::activeWeightTotal(),
                'weights_balanced'    => abs(EvaluationCriterion::activeWeightTotal() - 100) < 0.01,
            ],
            'top_students'        => Evaluation::where('evaluations.status', Evaluation::STATUS_APPROVED)
                ->join('students', 'students.student_id', '=', 'evaluations.student_id')
                ->join('users', 'users.user_id', '=', 'students.user_id')
                ->groupBy('users.user_id', 'users.full_name', 'students.matric_no')
                ->select('users.full_name', 'students.matric_no', DB::raw('ROUND(AVG(evaluations.total_score), 2) as average_score'))
                ->orderByDesc('average_score')->limit(5)->get(),
        ];
    }

    /** Average raw score per criterion across a set of evaluations, as a percentage too. */
    private function criterionBreakdown($evaluations): array
    {
        return $evaluations->flatMap->scores
            ->filter(fn ($s) => $s->criterion !== null)
            ->groupBy(fn ($s) => $s->criterion->name)
            ->map(function ($scores, $name) {
                $criterion = $scores->first()->criterion;
                $average   = round((float) $scores->avg('score'), 2);

                return [
                    'criterion'  => $name,
                    'category'   => $criterion->category,
                    'average'    => $average,
                    'max_score'  => $criterion->max_score,
                    'percentage' => $criterion->max_score > 0 ? round($average / $criterion->max_score * 100, 1) : null,
                    'responses'  => $scores->count(),
                ];
            })->values()->all();
    }
}