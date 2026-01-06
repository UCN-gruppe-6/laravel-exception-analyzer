<?php

namespace LaravelExceptionAnalyzer\Clients;

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
    public static function sendToExternalService(array $exception): void
    {
        // Implement sending to an external service if needed.
    }
}
