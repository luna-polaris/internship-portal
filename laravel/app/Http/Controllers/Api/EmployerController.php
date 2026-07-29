<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmployerProfileRequest;
use App\Models\Employer;
use Illuminate\Http\Request;

class EmployerController extends Controller
{
    public function show(Request $request)
    {
        $profile = Employer::with(['user', 'company'])->where('user_id', $request->user()->user_id)->first();

        if (! $profile) {
            return response()->json(['success' => false, 'message' => 'Employer profile not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $profile]);
    }

    public function update(UpdateEmployerProfileRequest $request)
    {
        $employer = Employer::where('user_id', $request->user()->user_id)->first();

        if (! $employer) {
            return response()->json(['success' => false, 'message' => 'Employer profile not found.'], 404);
        }

        $employer->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    public function index()
    {
        $employers = Employer::with(['user', 'company'])->get();

        return response()->json(['success' => true, 'data' => $employers]);
    }
}
