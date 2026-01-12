<?php
    /**
     * Laravel Exception Analyzer Facade
     *
     * This facade is the entry point that hooks our exception analyzer into Laravel's exception handling system.
     *
     * This is the piece that says:
     * "Whenever Laravel catches an exception, also send it through our exception analyzer pipeline."
     *
     * Without this class, our analyzer would never see real exceptions.
     */
namespace LaravelExceptionAnalyzer\Facades;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Facade;
use LaravelExceptionAnalyzer\Controller\ReportController;

/**
 * @see LaravelExceptionAnalyzer
 */
class LaravelExceptionAnalyzer extends Facade
{

    /**
     * Handles
     *
     * This method registers our exception reporting logic with Laravel's global exception handler.
     * Once this is set up:
     * Every uncaught exception in the application will automatically pass through ReportClient
     *
     * This is how real runtime exceptions enter our system.
     */
    public static function handles(?ExceptionHandler $exceptions = null): void
    {
        /**
         * This is what actually gets called when an exception is reported by Laravel.
         * Laravel passes the Throwable into this function.
         */
        $reportable = static function (\Throwable $exception): ?ReportController {
            // Read package configuration
            $config = config('laravel-exception-analyzer', []);
            // If the analyzer is disabled in config, we do absolutely nothing.
            if(($config['isEnabled'] ?? false) === false) {
                return null;
            }

             // Resolve the ReportController.
            $controller = app(ReportController::class);

            // Hand the exception over to our reporting pipeline
            $controller->report($exception);

            return $controller;
        };

        /**
         * There are two ways we can hook into Laravel’s exception handling, depending on context.
         */
        // Case 1: An ExceptionHandler instance is explicitly provided
        if ($exceptions) {
            $exceptions->reportable($reportable);
            return;
        }

        // Case 2: Resolve Laravel’s default exception handler
        $handler = app(ExceptionHandler::class);

        // Register the reportable callback if supported
        if (method_exists($handler, 'reportable')) {
            $handler->reportable($reportable);
            return;
        }
    }
}
