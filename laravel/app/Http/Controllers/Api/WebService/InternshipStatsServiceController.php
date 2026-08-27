<?php

namespace App\Http\Controllers\Api\WebService;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebService\GetInternshipStatsRequest;
use App\Models\Internship;
use App\Support\WebService\ServiceResponse;
use Illuminate\Http\JsonResponse;

/**
 * getInternshipStats — the web service the Internship module exposes.
 *
 * Aggregate posting figures for the admin dashboard. The dashboard lives in User
 * Management, which has no business counting rows in this module's table, so the
 * numbers are published here instead.
 *
 * Only totals cross the boundary: no titles, no company names, no ids. That keeps
 * the contract narrow and means the response is safe to cache or log in full.
 */
class InternshipStatsServiceController extends Controller
{
    public function getInternshipStats(GetInternshipStatsRequest $request): JsonResponse
    {
        $requestId = $request->validated()['requestId'];

        // One grouped query for the per-status counts rather than three COUNT(*)
        // round trips; the vacancy sum is the only extra query.
        $byStatus = Internship::selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return ServiceResponse::success([
            'totalInternships' => (int) $byStatus->sum(),
            'publishedInternships' => (int) $byStatus->get('Published', 0),
            'draftInternships' => (int) $byStatus->get('Draft', 0),
            'closedInternships' => (int) $byStatus->get('Closed', 0),
            'totalVacancies' => (int) Internship::sum('vacancies'),
        ], $requestId);
    }
}
