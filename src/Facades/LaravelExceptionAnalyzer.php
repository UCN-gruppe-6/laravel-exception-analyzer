<?php

namespace LaravelExceptionAnalyzer\Facades;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Facade;
use LaravelExceptionAnalyzer\Clients\ReportClient;

/**
 * @see use LaravelExceptionAnalyzer\LaravelExceptionAnalyzer;
 */
class LaravelExceptionAnalyzer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LaravelExceptionAnalyzer::class;
    }

    public static function handles(?ExceptionHandler $exceptions = null): void
    {
        $reportable = static function (\Throwable $exception): ?ReportClient {
            $config = config('laravel-exception-analyzer', []);

            if(($config['isEnabled'] ?? false) === false) {
                return null;
            }

            $reportClient = app(ReportClient::class);

            $reportClient->report($exception);

            // test comment

            return $reportClient;
        };

        if ($exceptions) {
            $exceptions->reportable($reportable);
            return;
        }

        $handler = app(ExceptionHandler::class);

        if (method_exists($handler, 'reportable')) {
            $handler->reportable($reportable);
            return;
        }

    }
}
