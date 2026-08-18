<?php

use Illuminate\Support\Str;

return [

    // Supported: "file", "cookie", "database", "memcached", "redis", "dynamodb", "array"
    'driver' => env('SESSION_DRIVER', 'database'),

    // Minutes a session may sit idle before expiring; see expire_on_close for browser-close behavior.
    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    'encrypt' => env('SESSION_ENCRYPT', false),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    // Must match one of the defined cache stores. Affects: "dynamodb", "memcached", "redis"
    'store' => env('SESSION_STORE'),

    // Chance (out of 100) that a driver sweeps expired sessions on a given request.
    'lottery' => [2, 100],

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    // If true, the cookie is only sent over HTTPS.
    'secure' => env('SESSION_SECURE_COOKIE'),

    // If true, the cookie isn't accessible via JavaScript.
    'http_only' => env('SESSION_HTTP_ONLY', true),

    // Mitigates CSRF. Supported: "lax", "strict", "none", null
    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    // Ties the cookie to the top-level site for cross-site contexts; requires secure + same_site=none.
    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
