<?php

/**
 * Laravel exception analyzer
 *
 * This file contains all configuration for the LaravelExceptionAnalyzer package used in our system.
 *
 * Instead of hardcoding values (API keys, endpoints, thresholds),
 * we collect everything here so it can be changed without touching code
 *
 * In short: this file controls how exception reporting behaves.
 */
return [
    // Enable/disable reporting
    'isEnabled' => env('LEA_ENABLED', false),

    /**
     * Client settings used by ReportClient
     *
     * Settings used when exceptions are sent out of the system (for example to an external service or API).
     * These values are read from environment variables so sensitive information is not stored in code.
     */
    'apiKey' => env('LEA_API_KEY', null),
    'endpoint' => env('LEA_ENDPOINT', null),

    // Metadata describing where the exception comes from.
    'project' => env('LEA_PROJECT', null),
    'environment' => env('LEA_ENV', env('APP_ENV', 'production')),

    // Slack integration settings.
    'SLACK_WEBHOOK_URL' => env('LEA_SLACK_WEBHOOK_URL', null),

    /**
     * These values control how exception frequency is evaluated.
     *
     * They are used to detect patterns such as:
     * - the same exception happening repeatedly
     * - many exceptions occurring within a short time window
     *
     * This helps decide when something is "just noise"
     * versus when it should trigger attention or alerts.
     */
    'CHECK_EXCEPTION_WITH_IN_MINUTES' => env('LEA_CHECK_EXCEPTION_WITH_IN_MINUTES', 5),
    'AMOUNT_OF_EXCEPTIONS_WITH_IN_TIME' => env('LEA_AMOUNT_OF_EXCEPTIONS_WITH_IN_TIME', 5),
    'AI_PROVIDER' => env('LEA_AI_PROVIDER', 'openai'),
    'AI_MODEL' => env('LEA_AI_MODEL', 'gpt-3.5-turbo'),

    'REPORT_EXCEPTIONS_API_URL' => env('LEA_REPORT_EXCEPTIONS_API_URL', null),

    // List of exception types that should be ignored.
    'ignore' => [
        // \Illuminate\Validation\ValidationException::class,
    ],
];
