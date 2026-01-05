<?php

/**
 * AI Client Command
 *
 * This Artisan command is a development / test command.
 *
 * It exists so we can manually test the AI classification pipeline
 * without waiting for real exceptions to happen.
 *
 * In short, this command:
 * - picks a known exception from the database
 * - sends it to the AI for classification
 * - logs the AI response
 * - stores the result as a structured exception

 * This command is not part of normal production flow.
 */

namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\AI\AiClient;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use LaravelExceptionAnalyzer\Models\StructuredExceptionModel;

class AIClientCommand extends Command
{
    /**
     * How the command is called from the terminal: php artisan send:AI
     */
    protected $signature = 'send:AI';
    protected $description = 'Send an exception payload to Gemini and print the response';

    /**
     * Execute the command.
     * This method runs when the command is called.
     */
    public function handle(): void
    {
        /**
         * 1. Fetch a specific exception from the database
         *
         * That is intentional for testing: we want a predictable exception and we want repeatable results
         * Only the fields relevant for AI classification are selected.
         */
        $exception = ExceptionModel::select(['id', 'message', 'type', 'code', 'file', 'line', 'url', 'hostname', 'user_id', 'session_id', 'level'])->where('id', 3)->get();

        // Log the raw exception so we can see exactly what is being sent to AI
        Log::info($exception);

        /**
         * 2. Resolve the AI client from the service container
         * This keeps the command decoupled from the actual AI implementation.
         */
        $aiClient = app(AiClient::class);

        /**
         * 3. Send the exception to the AI for classification
         */
        $response = $aiClient->classify($exception->first()->toArray());

        // Log the AI response for inspection/debugging
        Log::info(json_encode($response));

        /**
         * 4. Store the AI result as a structured exception
         * This simulates what the real pipeline will do automatically:
         * turning raw exceptions into structured, readable records.
         */
        StructuredExceptionModel::create($response);
    }
}
