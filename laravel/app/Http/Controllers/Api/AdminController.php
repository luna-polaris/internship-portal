<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employer;
use App\Models\Internship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboardStats()
    {
        // Single grouped query instead of four separate COUNT(*) calls.
        $byStatus = Internship::selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => Student::count(),
                'total_employers' => Employer::count(),
                'total_companies' => Company::count(),
                'pending_users' => User::where('status', 'Pending')->count(),

                'total_internships' => (int) $byStatus->sum(),
                'published_internships' => (int) $byStatus->get('Published', 0),
                'draft_internships' => (int) $byStatus->get('Draft', 0),
                'closed_internships' => (int) $byStatus->get('Closed', 0),
                'total_vacancies' => (int) Internship::sum('vacancies'),
            ],
        ]);
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
