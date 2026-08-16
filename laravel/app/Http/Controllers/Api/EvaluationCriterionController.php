<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationCriterionRequest;
use App\Models\EvaluationCriterion;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Function 1 — Performance Metrics and Criteria Definition.
 * Admin-only CRUD over the rubric every evaluation is scored against.
 */
class EvaluationCriterionController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    /** Employers need the active rubric to render the form; Admins manage the full list. */
    public function index(Request $request): JsonResponse
    {
        $query = EvaluationCriterion::query()->orderBy('category')->orderBy('name');

        if ($request->user()->role !== 'Admin') {
            $query->active();
        } elseif ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $criteria = $query->get();

        return response()->json([
            'data' => $criteria,
            'meta' => [
                'active_weight_total' => EvaluationCriterion::activeWeightTotal(),
                'weights_balanced'    => abs(EvaluationCriterion::activeWeightTotal() - 100) < 0.01,
            ],
        ]);
    }

    public function show(EvaluationCriterion $criterion): JsonResponse
    {
        return response()->json(['data' => $criterion]);
    }

    public function store(StoreEvaluationCriterionRequest $request): JsonResponse
    {
        $criterion = EvaluationCriterion::create(
            $request->validated() + ['created_by' => $request->user()->user_id]
        );

        return response()->json([
            'message' => 'Criterion added.',
            'data'    => $criterion,
            'meta'    => ['active_weight_total' => EvaluationCriterion::activeWeightTotal()],
        ], 201);
    }

    public function update(StoreEvaluationCriterionRequest $request, EvaluationCriterion $criterion): JsonResponse
    {
        $criterion->update($request->validated());
        $this->notifications->criteriaChanged($criterion->name);

        return response()->json([
            'message' => 'Criterion updated.',
            'data'    => $criterion->fresh(),
            'meta'    => ['active_weight_total' => EvaluationCriterion::activeWeightTotal()],
        ]);
    }

    /**
     * Criteria already used in an evaluation are deactivated instead of deleted —
     * removing one would silently change historical totals.
     */
    public function destroy(Request $request, EvaluationCriterion $criterion): JsonResponse
    {
        abort_unless($request->user()->role === 'Admin', 403);

        if ($criterion->scores()->exists()) {
            $criterion->update(['is_active' => false]);

            return response()->json([
                'message' => 'This criterion is used by existing evaluations, so it was deactivated instead of deleted. It no longer appears on new forms.',
                'data'    => $criterion->fresh(),
            ]);
        }

        $criterion->delete();

        return response()->json(['message' => 'Criterion deleted.']);
    }
}