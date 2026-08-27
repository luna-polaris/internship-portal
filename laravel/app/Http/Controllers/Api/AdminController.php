<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employer;
use App\Models\Internship;
use App\Models\Student;
use App\Models\User;
use App\Services\InternshipStatsClient;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Posting figures belong to the Internship module, so they are requested over the
    // Interface Agreement rather than queried here. localInternshipStats() covers the
    // window where that service is unavailable.
    public function dashboardStats(InternshipStatsClient $internshipStats)
    {
        $postings = $internshipStats->fetch() ?? $this->localInternshipStats();

        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => Student::count(),
                'total_employers' => Employer::count(),
                'total_companies' => Company::count(),
                'pending_users' => User::where('status', 'Pending')->count(),

                'total_internships' => $postings['totalInternships'],
                'published_internships' => $postings['publishedInternships'],
                'draft_internships' => $postings['draftInternships'],
                'closed_internships' => $postings['closedInternships'],
                'total_vacancies' => $postings['totalVacancies'],
            ],
        ]);
    }

    /**
     * Fallback used only when getInternshipStats cannot be reached. It reproduces the
     * same figures locally, which means it keeps the direct dependency on the
     * Internship module's table that the web service exists to remove — so this
     * method should go once that service is deployed for every developer.
     *
     * @return array{totalInternships:int, publishedInternships:int, draftInternships:int, closedInternships:int, totalVacancies:int}
     */
    private function localInternshipStats(): array
    {
        // Single grouped query instead of four separate COUNT(*) calls.
        $byStatus = Internship::selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'totalInternships' => (int) $byStatus->sum(),
            'publishedInternships' => (int) $byStatus->get('Published', 0),
            'draftInternships' => (int) $byStatus->get('Draft', 0),
            'closedInternships' => (int) $byStatus->get('Closed', 0),
            'totalVacancies' => (int) Internship::sum('vacancies'),
        ];
    }

    // Unlike the public /api/internships endpoint, this includes Draft and Closed postings.
    public function listInternships(Request $request)
    {
        $query = Internship::with('company:company_id,company_name')
            ->withCount('applications');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->query('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhereHas('company', fn ($c) => $c->where('company_name', 'like', $term));
            });
        }

        $perPage = min((int) $request->query('per_page', 15), 100) ?: 15;

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('internship_id')->paginate($perPage),
        ]);
    }

    public function listUsers(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json(['success' => true, 'data' => $query->orderByDesc('created_at')->get()]);
    }

    public function activateUser(User $user)
    {
        $user->update(['status' => 'Active']);

        return response()->json(['success' => true, 'message' => 'User activated.']);
    }

    public function deactivateUser(User $user)
    {
        $user->update(['status' => 'Inactive']);

        return response()->json(['success' => true, 'message' => 'User deactivated.']);
    }

    public function deleteUser(User $user)
    {
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted.']);
    }

}
