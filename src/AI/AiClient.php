<?php


namespace LaravelExceptionAnalyzer\AI;

use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\AI\AiClassificationResult;
use LaravelExceptionAnalyzer\AI\ExceptionSanitizer;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
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
     */
    public function classify(array $exceptionData): ?AiClassificationResult
    {
        /**
         * 1. Check if AI classification is enabled and api key
         */
        if (!(config('laravel-exception-analyzer.ai.enabled', env('LEA_AI_ENABLED'))) ||
            !(config('laravel-exception-analyzer.ai.apiKey', env('LEA_AI_API_KEY')))) {
            return null;
        }

        $payload = ExceptionSanitizer::sanitize($exceptionData);

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
        Return exactly one JSON object matching the schema below and nothing else. Do not include any explanation, text, code fences or extra fields.

        Classify the following exception into:
        - category
        - source
        - severity
        - status_message

            Exception:
            " . json_encode($payload, JSON_PRETTY_PRINT);


            $response = Prism::structured()
                ->using(Provider::Gemini, 'gemini-2.5-flash')
                ->withSchema($schema)
                ->withPrompt($prompt)
                ->asStructured();

        Log::info(json_encode($response->structured));

        $data = $response->structured;

        return AiClassificationResult::fromArray(is_array($data) ? $data : []);

    }
}
