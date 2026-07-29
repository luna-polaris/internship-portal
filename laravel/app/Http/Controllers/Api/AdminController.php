<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Employer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboardStats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => Student::count(),
                'total_employers' => Employer::count(),
                'total_companies' => Company::count(),
                'pending_users' => User::where('status', 'Pending')->count(),
            ],
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

    public function promoteAdmin(User $user)
    {
        $admin = Admin::where('user_id', $user->user_id)->first();

        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'This user is not an admin.'], 422);
        }

        $admin->promote();

        return response()->json(['success' => true, 'message' => 'Admin promoted to Super Admin.']);
    }

    public function demoteAdmin(User $user)
    {
        $admin = Admin::where('user_id', $user->user_id)->first();

        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'This user is not an admin.'], 422);
        }

        $admin->demote();

        return response()->json(['success' => true, 'message' => 'Admin demoted to Moderator.']);
    }
}
