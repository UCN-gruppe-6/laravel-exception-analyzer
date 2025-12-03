<?php

return [

    /**
     * Master switch that enables or disables the entire package.
     */
    'isEnabled' => env('LEA_ENABLED', true),

    /**
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
