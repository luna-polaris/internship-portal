<?php

return [

    'key' => env('WEBSERVICE_KEY'),

    'timestamp_format' => 'Y-m-d H:i:s',

    'peers' => [

        'internship_stats' => env('WS_INTERNSHIP_STATS_URL')
            ?: rtrim((string) env('APP_URL'), '/') . '/api/ws/internship-stats',
    ],

    'timeout' => (int) env('WS_TIMEOUT', 5),

];
