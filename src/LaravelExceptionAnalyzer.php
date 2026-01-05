<?php

/**
 * Laravel Exception Analyzer model
 *
 * Its job is very simple and very intentional:
 * it provides one clean method that the rest of the application can call to report an exception.
 *
 * It does not contain logic itself.
 * It delegates the real work to ReportClient.
 *
 * This separation exists so:
 * - the analyzer has a clear entry point
 * - the implementation can change without affecting callers
 * - the system stays testable and maintainable
 */

namespace LaravelExceptionAnalyzer;

use LaravelExceptionAnalyzer\Clients\ReportClient;
use Throwable;
class LaravelExceptionAnalyzer {

    /**
     * Constructor
     * We inject ReportClient instead of creating it manually.
     *
     * This allows proper dependency injection
     * ReportClient contains the real reporting pipeline:
     * storing exceptions, sending to AI, logging, etc.
     */
    public function __construct(
        private readonly ReportClient $reportClient,
    ) {}

    /**
     * Report()
     *
     * This method is called when we want to report an exception into the exception analyzer system.
     *
     * It simply forwards the exception to ReportClient.
     *
     * This method exists so callers do not need to know:
     * - how exceptions are stored
     * - whether AI is involved
     * - how the pipeline works internally
     *
     * They just call: report($exception)
     */
    public function report(Throwable $exception): void
    {
        $this->reportClient->report($exception);
    }
}
