<?php

// config for NikolajVE/LaravelExceptionAnalyzer
return [
    // Enable/disable reporting
    'isEnabled' => env('LEXA_ENABLED', false),

    // Client settings used by ReportClient
    'apiKey' => env('LEXA_API_KEY', null),
    'endpoint' => env('LEXA_ENDPOINT', null),

    // Metadata
    'project' => env('LEXA_PROJECT', null),
    'environment' => env('LEXA_ENV', env('APP_ENV', 'production')),

    // Optional: exceptions to ignore
    'ignore' => [
        // \Illuminate\Validation\ValidationException::class,
    ],
];
