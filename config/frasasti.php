<?php

// config for bale/frasasti

return [
    'whatsapp' => [
        'support_number' => env('FRASASTI_WA_SUPPORT_NUMBER', '6285126061182'),
    ],

    'aduan' => [
        'recaptcha_action' => env('FRASASTI_ADUAN_RECAPTCHA_ACTION', 'aduan'),
        'min_score' => (float) env('FRASASTI_ADUAN_RECAPTCHA_MIN_SCORE', 0.5),
        'max_attempts' => (int) env('FRASASTI_ADUAN_MAX_ATTEMPTS', 5),
        'decay_seconds' => (int) env('FRASASTI_ADUAN_DECAY_SECONDS', 60),
    ],

    'api' => [
        'per_page' => (int) env('FRASASTI_API_PER_PAGE', 50),
        'max_per_page' => (int) env('FRASASTI_API_MAX_PER_PAGE', 100),
    ],
];
