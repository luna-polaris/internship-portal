<?php

namespace App\Http\Controllers\Api\WebService;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebService\GetInternshipStatsRequest;
use App\Models\Internship;
use App\Support\WebService\ServiceResponse;
use Illuminate\Http\JsonResponse;


class InternshipStatsServiceController extends Controller
{
    public function getInternshipStats(GetInternshipStatsRequest $request): JsonResponse
    {
        $requestId = $request->validated()['requestId'];

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
