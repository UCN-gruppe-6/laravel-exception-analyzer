<?php
    /**
     * AiClient
     *
     * This class is the “AI connector” in our exception system.
     *
     * Our system stores raw exceptions in the database, then turns them into
     * structured exceptions that are easier to read and work with.
     * Instead of hand-writing all classification rules (carrier, severity,
     * short message, etc.), we let an AI model do that work.
     *
     * So this class has one job:
     * - send exception data to the AI
     * - force the AI to answer in a strict JSON format (schema)
     * - return that structured result back to our pipeline
     *
     * This class does NOT:
     * - catch exceptions
     * - store things in the database
     * - update models directly
     *
     * It only communicates with the AI and returns AIs output.
     */
namespace LaravelExceptionAnalyzer\Clients;

use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\AI\AiClassificationResult;
use LaravelExceptionAnalyzer\AI\ExceptionSanitizer;
use LaravelExceptionAnalyzer\Enums\Carrier;
use LaravelExceptionAnalyzer\Enums\Severity;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
class AIClient
{
    /**
     * CreateClfFromResponse
     *
     * We generate a CFL string that works like a simple fingerprint.
     * The goal is to have a stable value we can use to group exceptions
     * that are basically the "same kind of problem".
     *
     * In our system, we build it from: carrier + file name + line number
     * That gives us something like: GLS-CarrierService-142
     * This is later used to connect / group exceptions into repetitive issues.
     */
    private static function createCflFromResponse(array $response): array
    {
        $data = $response;
        if (isset($response['affected_carrier'])) {
            $data['cfl'] = $response['affected_carrier'] . '-' . $response['file_name'] . '-' . $response['line_number'];
        }
        return $data;
    }

    /**
     * This is the "classify one exception" step.
     *
     * Input: one raw exception (as an array)
     * Output: a structured classification result (as an array)
     *         containing things like carrier, severity, readable messages, etc.
     *
     * If AI is not enabled or no API key is configured, we return null,
     * because the pipeline should keep working even without AI.
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

        /**
         * 2. Define the exact JSON format we want back from the AI
         */
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

        /**
         * 3. Build a prompt that tells the AI exactly how to behave
         *
         * Important:
         * - We demand only JSON output (no explanation)
         * - We restate the rules for fields that often go wrong
         * - We attach the exception as JSON at the end
         */
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

            /**
             * 4. Send the prompt + schema to the AI provider via Prism
             * Prism is the library that handles the actual AI call.
             */
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

            /**
             * 5. Log what the AI returned
             */
            Log::info('AI Response: ' . json_encode($response->structured, JSON_PRETTY_PRINT));

            /**
             * 6. Add our CFL fingerprint and return the final structured array
             * The CFL is used later to group exceptions into repetitive exceptions.
             */
            return self::createCflFromResponse($response->structured);
    }
}
