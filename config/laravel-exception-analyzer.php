<?php

// config for NikolajVE/LaravelExceptionAnalyzer
return [
    // Enable/disable reporting
    'isEnabled' => env('LEA_ENABLED', false),

    // Client settings used by ReportClient
    'apiKey' => env('LEA_API_KEY', null),
    'endpoint' => env('LEA_ENDPOINT', null),

    // Metad
    'project' => env('LEA_PROJECT', null),
    'environment' => env('LEA_ENV', env('APP_ENV', 'production')),

    'SLACK_WEBHOOK_URL' => env('LEA_SLACK_WEBHOOK_URL', null),
    'CHECK_EXCEPTION_WITH_IN_MINUTES' => env('LEA_CHECK_EXCEPTION_WITH_IN_MINUTES', 5),
    'AMOUNT_OF_EXCEPTIONS_WITH_IN_TIME' => env('LEA_AMOUNT_OF_EXCEPTIONS_WITH_IN_TIME', 5),

    'ignore' => [
        // \Illuminate\Validation\ValidationException::class,
    ],
];
