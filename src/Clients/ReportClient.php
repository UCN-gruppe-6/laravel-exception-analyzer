<?php

namespace NikolajVE\LaravelExceptionAnalyzer\Clients;

use Throwable;
use NikolajVE\LaravelExceptionAnalyzer\AI\AiClient;

/**
 * ReportClient
 *
 * Coordinates the process of handling an exception by:
 * 1. Calling the AI service to classify the exception.
 * 2. (In the future) Persisting the result into the database.
 *
 * This class acts as the intermediary between the main analyzer service
 * and the underlying AI classification logic.
 */
class ReportClient
{
    public function __construct(
        private readonly AiClient $aiClient,
    ) {}

    /**
     * Handle and process the given exception.
     *
     * Current flow:
     * 1. Forward the exception to AiClient::classify().
     * 2. If a classification result is returned, log it temporarily.
     * 3. (Future step) Save both the exception and AI result in the database.
     * @param Throwable $exception  The exception captured by Laravel.
     */
    public function report(Throwable $exception): void
    {
        /**
         * 1. Call AI for classification.
         *
         * AiClient will:
         * - sanitize the exception,
         * - send it to the external AI endpoint,
         * - return an AiClassificationResult or null.
         */
        $result = $this->aiClient->classify($exception);

        /**
         * 2. Temporary: Log the classification result.
         *
         * This is a placeholder for future persistence logic.
         * Once the database schema is ready, we will store:
         * - the original exception data
         * - the AI classification metadata
         */
        if ($result !== null) {
            // midlertidig test: log resultatet
            logger()->info('AI classified exception', $result->toArray());
        }

        //2) TODO: Save exception to database
    }
}
