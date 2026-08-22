<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use Illuminate\Http\Request;

class PublicInternshipController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'q', 'category', 'city', 'state', 'work_mode', 'skills', 'min_allowance', 'max_allowance', 'min_duration', 'max_duration',
        ]);

        $query = Internship::visible()
            ->filter($filters)
            ->with('company:company_id,company_name,city,state,logo');

        match ($request->query('sort')) {
            'deadline' => $query->orderByRaw('application_deadline IS NULL')->orderBy('application_deadline'),
            'allowance' => $query->orderByRaw('allowance IS NULL')->orderByDesc('allowance'),
            default => $query->orderByDesc('published_at'),
        };

        $perPage = min((int) $request->query('per_page', 10), 100) ?: 10;

        return response()->json(['success' => true, 'data' => $query->paginate($perPage)]);
    }

    public function show(Internship $internship)
    {
        if (! Internship::visible()->whereKey($internship->internship_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Internship posting not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $internship->load('company')]);
    }
}
