<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewEvaluationRequest;
use App\Http\Requests\StoreEvaluationRequest;
use App\Models\Evaluation;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Visibility: employer sees everything they wrote; admin sees everything, filterable by status; student sees only their own once Approved.
class EvaluationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Evaluation::with(['student.user:user_id,full_name,email', 'employer.company:company_id,employer_id,company_name'])
            ->latest('updated_at');

        match ($user->role) {
            'Employer' => $query->where('employer_id', $user->employer?->employer_id),
            'Student'  => $query->where('student_id', $user->student?->student_id)->visibleToStudent(),
            'Admin'    => $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status'))),
            default    => abort(403),
        };

        return response()->json($query->paginate(15));
    }

    public function show(Request $request, Evaluation $evaluation): JsonResponse
    {
        $this->authorizeView($request, $evaluation);

        $evaluation->load([
            'scores.criterion',
            'student.user:user_id,full_name,email',
            'employer.company:company_id,employer_id,company_name',
            'reviewer:user_id,full_name',
        ]);

        return response()->json(['data' => $evaluation]);
    }

    // action=save keeps it as a Draft; action=submit locks it and queues it for admin review.
    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        $employer = $request->user()->employer;
        abort_unless($employer, 403, 'Complete your employer profile first.');

        $data = $request->validated();

        $evaluation = DB::transaction(function () use ($data, $employer) {
            $evaluation = Evaluation::firstOrNew([
                'student_id'  => $data['student_id'],
                'employer_id' => $employer->employer_id,
                'period'      => $data['period'],
            ]);

            if ($evaluation->exists && ! $evaluation->isEditable()) {
                abort(422, 'This evaluation has already been submitted and can no longer be edited.');
            }

            $evaluation->fill([
                'application_id'  => $data['application_id'] ?? $evaluation->application_id,
                'strengths'       => $data['strengths'] ?? null,
                'improvements'    => $data['improvements'] ?? null,
                'overall_comment' => $data['overall_comment'] ?? null,
                'status'          => $data['action'] === 'submit' ? Evaluation::STATUS_SUBMITTED : Evaluation::STATUS_DRAFT,
                'submitted_at'    => $data['action'] === 'submit' ? now() : null,
                'review_remark'   => null,
                'reviewed_by'     => null,
                'reviewed_at'     => null,
            ])->save();

            foreach ($data['scores'] as $row) {
                $evaluation->scores()->updateOrCreate(
                    ['criterion_id' => $row['criterion_id']],
                    ['score' => $row['score'], 'remark' => $row['remark'] ?? null]
                );
            }

            $evaluation->recalculateTotalScore();

            return $evaluation;
        });

        if ($data['action'] === 'submit') {
            $student = Student::with('user:user_id,full_name')->find($data['student_id']);
            $this->notifications->evaluationSubmitted(
                $evaluation->evaluation_id,
                $student?->user?->full_name ?? 'a student',
                $employer->company?->company_name ?? $request->user()->full_name
            );
        }

        return response()->json([
            'message' => $data['action'] === 'submit'
                ? 'Evaluation submitted for review.'
                : 'Draft saved.',
            'data'    => $evaluation->load('scores.criterion'),
        ], $evaluation->wasRecentlyCreated ? 201 : 200);
    }

    public function review(ReviewEvaluationRequest $request, Evaluation $evaluation): JsonResponse
    {
        if ($evaluation->status !== Evaluation::STATUS_SUBMITTED) {
            return response()->json([
                'message' => "Only submitted evaluations can be reviewed. This one is {$evaluation->status}.",
            ], 422);
        }

        $approved = $request->input('decision') === 'approve';

        $evaluation->update([
            'status'        => $approved ? Evaluation::STATUS_APPROVED : Evaluation::STATUS_REJECTED,
            'reviewed_by'   => $request->user()->user_id,
            'reviewed_at'   => now(),
            'review_remark' => $request->input('remark'),
        ]);

        $evaluation->load(['student.user', 'employer.user', 'employer.company']);

        if ($approved) {
            $this->notifications->evaluationApproved(
                $evaluation->student->user->user_id,
                $evaluation->evaluation_id,
                $evaluation->employer->company?->company_name ?? 'Your host company'
            );
        } else {
            $this->notifications->evaluationRejected(
                $evaluation->employer->user->user_id,
                $evaluation->evaluation_id,
                $request->input('remark')
            );
        }

        return response()->json([
            'message' => $approved ? 'Evaluation approved and released to the student.' : 'Evaluation returned to the employer.',
            'data'    => $evaluation,
        ]);
    }

    public function destroy(Request $request, Evaluation $evaluation): JsonResponse
    {
        abort_unless(
            $request->user()->role === 'Employer'
                && $evaluation->employer_id === $request->user()->employer?->employer_id
                && $evaluation->status === Evaluation::STATUS_DRAFT,
            403,
            'Only your own drafts can be deleted.'
        );

        $evaluation->delete();

        return response()->json(['message' => 'Draft deleted.']);
    }

    private function authorizeView(Request $request, Evaluation $evaluation): void
    {
        $user = $request->user();

        $allowed = match ($user->role) {
            'Admin'    => true,
            'Employer' => $evaluation->employer_id === $user->employer?->employer_id,
            'Student'  => $evaluation->student_id === $user->student?->student_id
                          && $evaluation->status === Evaluation::STATUS_APPROVED,
            default    => false,
        };

        abort_unless($allowed, 403, 'You do not have access to this evaluation.');
    }
}