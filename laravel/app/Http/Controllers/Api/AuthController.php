<?php

namespace App\Http\Controllers\Api;

use App\Factories\ProfileFactoryResolver;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\Company;
use App\Models\Employer;
use App\Models\Student;
use App\Models\User;
use App\Support\EmailMasker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'full_name' => trim($data['full_name']),
                'email' => strtolower(trim($data['email'])),
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'],
                'status' => 'Pending',
            ]);

            $factory = ProfileFactoryResolver::forRole($data['role']);
            $profile = $factory->createProfile();
            $profile->user_id = $user->user_id;

            if ($profile instanceof Student) {
                $profile->matric_no = $data['matric_no'];
                $profile->university = $data['university'] ?? null;
                $profile->faculty = $data['faculty'] ?? null;
                $profile->programme = $data['programme'] ?? null;
                $profile->cgpa = $data['cgpa'] ?? null;
                $profile->graduation_year = $data['graduation_year'] ?? null;
            } elseif ($profile instanceof Employer) {
                $profile->position = $data['position'] ?? null;
                $profile->department = $data['department'] ?? null;
            }

            $profile->save();

            if ($profile instanceof Employer) {
                Company::create([
                    'employer_id' => $profile->employer_id,
                    'company_name' => $data['company_name'],
                    'registration_no' => $this->generateRegistrationNumber(),
                    'industry' => $data['industry'] ?? null,
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'postcode' => $data['postcode'] ?? null,
                    'website' => $data['website'] ?? null,
                    'company_email' => $data['company_email'] ?? null,
                    'company_phone' => $data['company_phone'] ?? null,
                    'description' => $data['description'] ?? null,
                ]);
            }

            $token = Str::random(64);
            $user->forceFill([
                'token' => $token,
                'token_expires_at' => now()->addHours(24),
            ])->save();

            return $user;
        });

        $verificationLink = route('verify-email', ['token' => $user->token]);
        Mail::raw("Verify your account: {$verificationLink}", function ($message) use ($user) {
            $message->to($user->email)->subject('Verify your InternHub account');
        });

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your email to activate your account.',
            'user_id' => $user->user_id,
            'masked_email' => EmailMasker::mask($user->email),
        ], 201);
    }

    // Admins use adminLogin() instead, so the privileged path isn't reachable via this form.
    public function login(LoginRequest $request)
    {
        $email = strtolower(trim($request->input('email')));
        $user = User::where('email', $email)->where('role', '!=', 'Admin')->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
        }

        if (! Hash::check($request->input('password'), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
        }

        if ($user->status === 'Pending') {
            return response()->json(['success' => false, 'message' => 'Please verify your email before logging in.'], 403);
        }

        if ($user->status === 'Inactive') {
            return response()->json(['success' => false, 'message' => 'Your account has been deactivated. Contact support.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'role' => $user->role,
            'token' => $token,
        ]);
    }

    // separate from login(): username-based and Admin-only.
    public function adminLogin(AdminLoginRequest $request)
    {
        $username = trim($request->input('username'));
        $user = User::where('username', $username)->where('role', 'Admin')->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid admin username or password.'], 401);
        }

        if ($user->status !== 'Active') {
            return response()->json(['success' => false, 'message' => 'This admin account is not active.'], 403);
        }

        $token = $user->createToken('admin-api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Admin login successful.',
            'role' => $user->role,
            'token' => $token,
        ]);
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }

    public function verifyEmail()
    {
        $token = request()->query('token');

        if (empty($token)) {
            return response()->json(['success' => false, 'message' => 'Verification token is required.'], 422);
        }

        $user = User::activateByToken($token);

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired verification link.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Email verified. You can now log in.']);
    }

    public function requestPasswordReset()
    {
        $email = strtolower(trim(request()->input('email', '')));
        $user = User::where('email', $email)->first();

        if ($user) {
            $token = Str::random(64);
            $user->forceFill(['token' => $token, 'token_expires_at' => now()->addHour()])->save();

            $resetLink = url("/api/reset-password?token={$token}");
            Mail::raw("Reset your password: {$resetLink}", function ($message) use ($user) {
                $message->to($user->email)->subject('Reset your InternHub password');
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'If that email is registered, a reset link has been sent.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where('token', $request->input('token'))
            ->where('token_expires_at', '>=', now())
            ->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired reset link.'], 404);
        }

        $user->forceFill([
            'password' => $request->input('password'),
            'token' => null,
            'token_expires_at' => null,
        ])->save();

        return response()->json(['success' => true, 'message' => 'Password has been reset. You can now log in.']);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->input('old_password'), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $user->forceFill(['password' => $request->input('new_password')])->save();

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }

    private function generateRegistrationNumber(): string
    {
        $last = Company::orderByDesc('company_id')->value('registration_no');
        $next = 1;

        if ($last && preg_match('/R(\d+)/', $last, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        return 'R' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
