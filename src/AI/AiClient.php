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
    public function classify(array $exceptionData): ?array
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
                new StringSchema('affected_carrier', 'What carrier is the exception on'),
                new StringSchema('is_internal', 'Boolean whether the exception is internal'),
                new StringSchema('severity', 'Severity level: low, medium, high'),
                new StringSchema('concrete_error_message', 'Short human-readable summary'),
                new StringSchema('full_readable_error_message', 'long technical summary'),
                new StringSchema('exception_id', 'id of the exception'),
                new StringSchema('user_id', 'id of the user'),
            ],
            requiredFields: ['affected_carrier', 'is_internal', 'severity', 'concrete_error_message', 'full_readable_error_message', 'exception_id', 'user_id'],
        );

        $prompt = "
        You are an exception classification engine.
        Return exactly one JSON object matching the schema below and nothing else. Do not include any explanation, text, code fences or extra fields.

        Classify the following exception into:
            -affected_carrier
            -is_internal
            -severity
            -concrete_error_message
            -full_readable_error_message
            -exception_id
            -user_id

            Exception:
            " . json_encode($payload, JSON_PRETTY_PRINT);


            $response = Prism::structured()
                ->using(Provider::Gemini, 'gemini-2.5-flash')
                ->withSchema($schema)
                ->withPrompt($prompt)
                ->asStructured();

            Log::info($response->structured);

            $data = $response->structured;

            $data['exception_id'] = 5;

            $data['user_id'] = 5;

            $data['is_internal'] = (boolean)$data['is_internal'];

            return $data;

        //        return AiClassificationResult::fromArray(is_array($data) ? $data : []);

    }
}
