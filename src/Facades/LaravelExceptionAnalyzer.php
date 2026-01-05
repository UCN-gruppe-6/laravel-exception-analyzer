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
use LaravelExceptionAnalyzer\Clients\ReportClient;

/**
 * @see LaravelExceptionAnalyzer
 */
class LaravelExceptionAnalyzer extends Facade
{
    /**
     * This tells Laravel which class the facade represents.
     *
     * In this case, the facade points back to itself.
     * This allows us to call LaravelExceptionAnalyzer::handles()
     * statically, even though real logic happens elsewhere.
     */
    protected static function getFacadeAccessor(): string
    {
        return LaravelExceptionAnalyzer::class;
    }

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
        $reportable = static function (\Throwable $exception): ?ReportClient {
            // Read package configuration
            $config = config('laravel-exception-analyzer', []);

            /**
             * If the analyzer is disabled in config, we do absolutely nothing.
             */
            if(($config['isEnabled'] ?? false) === false) {
                return null;
            }

            /**
             * Resolve the ReportClient.
             * ReportClient is responsible for: storing the exception, sending it to AI, triggering further processing
             */
            $reportClient = app(ReportClient::class);

            // Hand the exception over to our reporting pipeline
            $reportClient->report($exception);

            return $reportClient;
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
