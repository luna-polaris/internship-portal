<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shared service key
    |--------------------------------------------------------------------------
    |
    | Module-to-module calls are server-to-server: there is no signed-in user to
    | authenticate, so every caller presents this shared secret in the
    | X-Service-Key header instead. Set WEBSERVICE_KEY in .env; the same value is
    | given to every team member whose module consumes a service here.
    |
    */

    'key' => env('WEBSERVICE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Timestamp format
    |--------------------------------------------------------------------------
    |
    | The format the Interface Agreement fixes for every timeStamp field, on both
    | the request and the response side.
    |
    */

    'timestamp_format' => 'Y-m-d H:i:s',

    /*
    |--------------------------------------------------------------------------
    | Services this module consumes
    |--------------------------------------------------------------------------
    |
    | Endpoints owned by other modules. Kept in config rather than hard-coded so
    | each developer can point at a teammate's machine without editing code.
    |
    */

    'peers' => [
        // ?: rather than an env() default, so a key that is present but blank still
        // falls back instead of resolving to an empty URL.
        'internship_stats' => env('WS_INTERNSHIP_STATS_URL')
            ?: rtrim((string) env('APP_URL'), '/') . '/api/ws/internship-stats',
    ],

    'timeout' => (int) env('WS_TIMEOUT', 5),

];
