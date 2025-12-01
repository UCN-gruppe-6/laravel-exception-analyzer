<?php


namespace LaravelExceptionAnalyzer\AI;

use LaravelExceptionAnalyzer\AI\AiClassificationResult;
use LaravelExceptionAnalyzer\AI\ExceptionSanitizer;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

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
        if (!($config['enabled'] ?? false)) {
            return null;
        }

        /**
         * 2. Ensure required configuration fields are available.
         *
         * AI classification cannot proceed without:
         * - API key (authentication)
         * - Endpoint (destination URL)
         */
        if (empty($config['api_key'])) {
            return null;
        }

        $payload = ExceptionSanitizer::sanitize($exceptionData);

        $client = Prism::client($config['api_key']);

        $schema = new ObjectSchema(
            name: 'exception_classification',
            description: 'Classification of a Laravel exception',
            properties: [
                new StringSchema('category', 'High-level category'),
                new StringSchema('source', 'Source/system of the exception'),
                new StringSchema('severity', 'Severity level'),
                new StringSchema('status_message', 'Short human-readable summary'),
            ],
            requiredFields: ['category', 'source', 'severity', 'status_message'],
        );

        $prompt = "
            You are an exception classification engine.
            Classify the following exception into:
            - category
            - source
            - severity
            - status_message

            Exception:
            " . json_encode($payload, JSON_PRETTY_PRINT);

        $response = $client->structured()
            ->schema($schema)
            ->prompt($prompt)
            ->generate();

        if (!$response->valid()) {
            return null;
        }

        $data = $response->output();

        return AiClassificationResult::fromArray(is_array($data) ? $data : []);

    }
}
