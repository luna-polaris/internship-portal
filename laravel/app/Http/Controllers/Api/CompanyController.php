<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\Employer;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * An employer creates their company profile. `companies.employer_id`
     * is UNIQUE, so each employer can only ever have one company.
     */
    public function store(StoreCompanyRequest $request)
    {
        $employer = Employer::where('user_id', $request->user()->user_id)->first();

        if (! $employer) {
            return response()->json(['success' => false, 'message' => 'Employer profile not found.'], 404);
        }

        if ($employer->company()->exists()) {
            return response()->json(['success' => false, 'message' => 'A company profile already exists for this employer.'], 422);
        }

        $company = Company::create($request->validated() + ['employer_id' => $employer->employer_id]);

        return response()->json(['success' => true, 'message' => 'Company profile created.', 'data' => $company], 201);
    }

    public function update(UpdateCompanyRequest $request)
    {
        $employer = Employer::where('user_id', $request->user()->user_id)->first();
        $company = $employer ? $employer->company : null;

        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Company profile not found.'], 404);
        }

        $company->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Company profile updated.']);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $employer = Employer::where('user_id', $request->user()->user_id)->first();
        $company = $employer ? $employer->company : null;

        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Company profile not found.'], 404);
        }

        $path = $request->file('logo')->store('logos', 'public');
        $company->update(['logo' => $path]);

        return response()->json(['success' => true, 'message' => 'Logo uploaded.', 'path' => $path]);
    }

    public function show(Company $company)
    {
        return response()->json(['success' => true, 'data' => $company->load('employer.user')]);
    }

    public function index()
    {
        return response()->json(['success' => true, 'data' => Company::with('employer.user')->get()]);
    }

    public function search(Request $request)
    {
        $keyword = (string) $request->query('q', '');

        return response()->json(['success' => true, 'data' => Company::search($keyword)->get()]);
    }

    /**
     * Admin-only: remove a company (does not delete the employer/user).
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return response()->json(['success' => true, 'message' => 'Company deleted.']);
    }
}
