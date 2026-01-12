<?php
/**
 * This controller is responsible for coordinating the AI-based
 * analysis of exceptions stored in the system.
 *
 * It retrieves recent exceptions from the database, sends a
 * controlled subset of exception data to the AI client, and
 * persists the structured AI response for further processing.
 */
namespace LaravelExceptionAnalyzer\Controller;

use Illuminate\Support\Carbon;
use LaravelExceptionAnalyzer\Clients\AIClient;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use LaravelExceptionAnalyzer\Models\StructuredExceptionModel;

class AIController
{
    /**
     * Analyze recent exceptions using an AI model.
     *
     * This method:
     * 1. Retrieves exceptions created within a configurable time window.
     * 2. Sends each exception to the AI client for classification.
     * 3. Stores the AI-structured result in the database.
     *
     * The time window is configurable, allowing the analysis
     * frequency to be adjusted without code changes.
     */
    public function analyzeExceptions(): void
    {
        // Fetch only the required fields from the exception table.
        $exceptions =
            ExceptionModel::select(['id', 'message', 'type', 'code', 'file', 'line', 'url', 'hostname', 'user_id', 'session_id', 'level'])
                ->where('created_at', '>', Carbon::now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES')))
                ->get();

        // Resolve the AI client via Laravel’s service container.
        $aiClient = app(AIClient::class);

         // Send each exception to the AI for classification
         // and persist the structured result.
        foreach ($exceptions as $exception) {
            $response = $aiClient->classify($exception->toArray());

            // The AI response is stored in a separate model
            StructuredExceptionModel::create($response);
        }
    }

}
