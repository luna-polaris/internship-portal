<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInternshipRequest;
use App\Http\Requests\UpdateInternshipRequest;
use App\Models\Employer;
use App\Models\Internship;
use Illuminate\Http\Request;

class InternshipController extends Controller
{
    /**
     * Resolve the internship postings owned by the authenticated employer's company.
     */
    private function ownedCompany(Request $request)
    {
        $employer = Employer::where('user_id', $request->user()->user_id)->first();

        return $employer ? $employer->company : null;
    }

    public function index(Request $request)
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Company profile not found.'], 404);
        }

        $query = $company->internships()
            ->withCount([
                'applications',
                'applications as accepted_applications_count' => fn ($q) => $q->where('status', 'Accepted'),
            ])
            ->latest('internship_id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(StoreInternshipRequest $request)
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Company profile not found.'], 404);
        }

        $internship = $company->internships()->create($request->validated() + ['status' => 'Draft']);

        return response()->json(['success' => true, 'message' => 'Internship posting created.', 'data' => $internship], 201);
    }

    public function show(Request $request, Internship $internship)
    {
        $company = $this->ownedCompany($request);

        if (! $company || $internship->company_id !== $company->company_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to view this posting.'], 403);
        }

        return response()->json(['success' => true, 'data' => $internship]);
    }

    public function update(UpdateInternshipRequest $request, Internship $internship)
    {
        $company = $this->ownedCompany($request);

        if (! $company || $internship->company_id !== $company->company_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to edit this posting.'], 403);
        }

        $internship->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Internship posting updated.']);
    }

    public function publish(Request $request, Internship $internship)
    {
        $company = $this->ownedCompany($request);

        if (! $company || $internship->company_id !== $company->company_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to publish this posting.'], 403);
        }

        if (! in_array($internship->status, ['Draft', 'Closed'], true)) {
            return response()->json(['success' => false, 'message' => 'Only draft or closed postings can be published.'], 422);
        }

        $internship->update(['status' => 'Published', 'published_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Internship posting published.']);
    }

    public function close(Request $request, Internship $internship)
    {
        $company = $this->ownedCompany($request);

        if (! $company || $internship->company_id !== $company->company_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to close this posting.'], 403);
        }

        if ($internship->status !== 'Published') {
            return response()->json(['success' => false, 'message' => 'Only published postings can be closed.'], 422);
        }

        $internship->update(['status' => 'Closed']);

        return response()->json(['success' => true, 'message' => 'Internship posting closed.']);
    }

    public function destroy(Request $request, Internship $internship)
    {
        $company = $this->ownedCompany($request);

        if (! $company || $internship->company_id !== $company->company_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to delete this posting.'], 403);
        }

        if ($internship->status !== 'Draft') {
            return response()->json(['success' => false, 'message' => 'Published or closed postings cannot be deleted.'], 422);
        }

        $internship->delete();

        return response()->json(['success' => true, 'message' => 'Internship posting deleted.']);
    }
}
