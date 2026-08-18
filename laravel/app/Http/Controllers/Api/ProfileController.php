<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBasicInfoRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Models\Admin;
use App\Models\Employer;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

// Generic "my account" controller that dispatches to the correct role-specific model; role-specific fields still live in StudentController/EmployerController/CompanyController.
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $model = match ($user->role) {
            'Student' => Student::with('user')->where('user_id', $user->user_id)->first(),
            'Employer' => Employer::with('user')->where('user_id', $user->user_id)->first(),
            'Admin' => Admin::with('user')->where('user_id', $user->user_id)->first(),
            default => null,
        };

        if (! $model) {
            return response()->json(['success' => false, 'message' => 'Profile not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $model]);
    }

    public function updateBasicInfo(UpdateBasicInfoRequest $request)
    {
        $request->user()->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    public function updateEmail(UpdateEmailRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['email' => strtolower(trim($request->input('email')))]);

        return response()->json(['success' => true, 'message' => 'Email updated successfully.']);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['profile_picture' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated.',
            'profile_picture_url' => $user->fresh()->profile_picture_url,
        ]);
    }
}
