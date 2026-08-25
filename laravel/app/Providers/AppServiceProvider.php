<?php

namespace App\Providers;

use App\Models\Internship;
use App\Models\Student;
use App\Observers\InternshipObserver;
use App\Observers\StudentObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Student::observe(StudentObserver::class);
        Internship::observe(InternshipObserver::class);

        $this->configureRateLimiting();
    }

    /**
     * Throttles for the credential-handling endpoints. Each limiter is keyed twice:
     * once on the identity being attacked, so one account can't be ground down, and
     * once on the source address, so a single client can't spray many accounts.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $identity = strtolower(trim((string) ($request->input('email') ?? $request->input('username') ?? '')));

            return [
                Limit::perMinute(5)->by('id:' . $identity),
                Limit::perMinute(20)->by('ip:' . $request->ip()),
            ];
        });

        // Deliberately tighter: this endpoint sends mail and is the usual probe for account enumeration.
        RateLimiter::for('password-forgot', function (Request $request) {
            return [
                Limit::perMinute(3)->by('id:' . strtolower(trim((string) $request->input('email')))),
                Limit::perMinute(10)->by('ip:' . $request->ip()),
            ];
        });

        // Verification and reset links carry a token rather than an identity, so these can only be keyed on the caller.
        RateLimiter::for('token', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
