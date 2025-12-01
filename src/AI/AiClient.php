<?php


namespace NikolajVE\LaravelExceptionAnalyzer\AI;

use Throwable;
use Illuminate\Support\Facades\Http;

/**
 * AiClient
 *
 * Responsible for sending sanitized exception data to the external AI service
 * and converting the response into a structured AiClassificationResult.
 *
 * This class acts as the communication layer between the package and the AI model.
 */
class AiClient
{
    public function __construct(
        private readonly ExceptionSanitizer $sanitizer,
    ) {}

    /**
     * Sends a sanitized exception to the AI service and returns the classification result.
     *
     * Flow:
     * 1. Read and validate AI configuration.
     * 2. Sanitize the exception to avoid leaking sensitive data.
     * 3. Perform an authenticated HTTP POST request to the AI endpoint.
     * 4. Validate and parse the JSON response.
     * 5. Convert the response into an AiClassificationResult.
     *
     * Returns null if classification is disabled, config is incomplete, or API request fails.
     */
    public function classify(array $exceptionData): ?AiClassificationResult
    {
        // Fetch the AI config block from our package config file
        $config = config('laravel-exception-analyzer.ai');

        /**
         * 1. Check if AI classification is enabled.
         *
         * If disabled, the system should behave gracefully and simply skip AI processing.
         */
        if(!($config['enabled'] ?? false)) {
            return null;
        }

        /**
         * 2. Ensure required configuration fields are available.
         *
         * AI classification cannot proceed without:
         * - API key (authentication)
         * - Endpoint (destination URL)
         */
        if(empty($config['api_key']) || empty($config['endpoint'])) {
            return null;
        }

        /**
         * 3. Sanitize the exception payload.
         *
         * This prevents sensitive or irrelevant information from being sent to the AI.
         */
        $payload = $this->sanitizer->sanitize($exceptionData);

        /**
         * 4. Send the sanitized payload to the AI model.
         *
         * The request:
         * - is authenticated with the API key
         * - includes a timeout
         * - wraps the sanitized exception data inside the "exception" key
         */
        $response = Http::withToken($config['api_key'])
            ->timeout($config['timeout'] ?? 5)
            ->post($config['endpoint'],
                ['exception' => $payload
                ]);

        /**
         * 5. Validate the HTTP response.
         *
         * If the AI service returns any non-2xx status, we do not attempt to parse the output.
         */
        if(!$response->successful()) {
            return null;
        }

        // Retrieve JSON data from the response body
        $data = $response->json();

        // Defensive: Ensure the response is an associative array
        if (!is_array($data)) {
            return null;
        }


        return AiClassificationResult::fromArray($data);


    }
}
