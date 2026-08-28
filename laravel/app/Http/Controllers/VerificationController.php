<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class VerificationController extends Controller
{
    public function show(Request $request): RedirectResponse
    {
        $token = (string) $request->query('token', '');
        $user = $token !== '' ? User::activateByToken($token) : null;

        if (! $user) {
            return redirect()->route('login')->with('verification_failed', 'That verification link is invalid or has expired.');
        }

        return redirect()->route('login')->with('verified', 'Your email has been verified. You can now log in.');
    }
}
