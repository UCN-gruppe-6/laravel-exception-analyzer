<?php


namespace LaravelExceptionAnalyzer\AI;

use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\AI\AiClassificationResult;
use LaravelExceptionAnalyzer\AI\ExceptionSanitizer;
use LaravelExceptionAnalyzer\Enums\Carrier;
use LaravelExceptionAnalyzer\Enums\Severity;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\NumberSchema;
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
    private static function castFromResponse(array $response): array
    {
        $data = $response;
        $data['exception_id'] = isset($response['exception_id']) ? (int)$response['exception_id'] : null;
        $data['user_id']      = isset($response['user_id']) ? (int)$response['user_id'] : null;
        $data['is_internal']  = (boolean)$response['is_internal'];
        return $data;
    }

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

        $schema = new ObjectSchema(
            name: 'exception_classification',
            description: 'Classification of a Laravel exception',
            properties: [
                new EnumSchema('affected_carrier', 'What carrier is the exception on. If you are unable to find any Carriers matching these, return null', Carrier::toArray(), nullable: true),
                new BooleanSchema('is_internal', 'Boolean whether the exception is internal. True if the exception is caused by internal code such as syntax error, false if it is caused by external code like a package, third party service or API. Use the Code to determine this'),
                new EnumSchema('severity', 'Severity level of the exception', Severity::toArray()),
                new StringSchema('full_readable_error_message', 'long technical summary. Should include message, type, code, file, line and any other relevant information from the exception data provided'),
                new NumberSchema('exception_id', 'id of the exception, is sent as "id"'),
                new NumberSchema('user_id', 'id of the user, is sent as "user_id", can be null', true),
                new StringSchema('line_number', 'line number where the exception occurred'),
                new StringSchema('code', 'The code which you have received'),
                new StringSchema('type', 'Return ONLY the class name after the last backslash (e.g., "App\\Exceptions\\Carrier\\CarrierException becomes CarrierException")'),
                new StringSchema('file_name', 'Return ONLY the filename without the full path'),
                new StringSchema('concrete_error_message', 'Max 3 words'),
            ],
            requiredFields: ['affected_carrier', 'is_internal', 'severity', 'concrete_error_message', 'full_readable_error_message', 'exception_id', 'user_id', 'line_number', 'code', 'type', 'file_name']
        );

        $prompt = "
        You are an exception classification engine.
        Return exactly one JSON object matching the schema below and nothing else. Do not include any explanation, text, code fences or extra fields.

        IMPORTANT RULES:
        - For 'carrier': What carrier is the exception on. If you are unable to find any Carriers matching these, return null.
        - For 'type': Return ONLY the class name after the last backslash (e.g., 'App\\Exceptions\\Carrier\\CarrierException' becomes 'CarrierException')
        - For 'file_name': Return ONLY the filename without the full path and .php
        - For 'line_number': Return the line number as a string
        - For 'is_internal': Use standard HTTP codes to determine this, if less than 500 then error is internal.
        - For 'affected_carrier': What carrier is the exception on. If you are unable to find any Carriers matching these, return null.
        - For 'concrete_error_message': Provide a very short summary of the error in max 3 words.

            Exception:
            " . json_encode($exceptionData, JSON_PRETTY_PRINT);


            $response = Prism::structured()
                ->using(Provider::Ollama, 'mistral:latest')
                ->withSchema($schema)
                ->withPrompt($prompt)
                ->withClientOptions(
                    [
                        'timeout' => 300,
                    ]
                )
                ->asStructured();

            Log::info('AI Response: ' . json_encode($response->structured, JSON_PRETTY_PRINT));

            return self::castFromResponse($response->structured);
    }
}
