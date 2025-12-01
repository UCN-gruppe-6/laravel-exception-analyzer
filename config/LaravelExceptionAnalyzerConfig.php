<?php

/**
 * Configuration file for NikolajVE/LaravelExceptionAnalyzer
 *
 * This file defines the global settings for the exception analyzer package.
 * Users can override these values in their application's `.env` file.
 */

return [

    /**
     * Master switch that enables or disables the entire package.
     *
     * When set to false:
     * - Exceptions will NOT be processed by the analyzer.
     * - No AI calls will be made.
     * - The exception pipeline hook will be inactive.
     *
     * Useful in production environments, debugging sessions,
     * or when the AI backend is temporarily unavailable.
     */
    'isEnabled' => env('LEA_ENABLED', true),

    /**
     * Configuration for the AI classification service.
     *
     * These settings control whether AI classification is active,
     * and define how the package communicates with the external AI endpoint.
     */
    'ai' => [
        'enabled'  => env('LEA_AI_ENABLED', true),
        'api_key'  => env('LEA_AI_API_KEY'),
        'endpoint' => env('LEA_AI_ENDPOINT', 'https://example.test/ai'), // fx jeres backend AI-service
        'timeout'  => env('LEA_AI_TIMEOUT', 5),
    ],

];
