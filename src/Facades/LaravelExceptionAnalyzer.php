<?php

namespace LaravelExceptionAnalyzer\Facades;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Facade;

/**
 * @see LaravelExceptionAnalyzer\LaravelExceptionAnalyzer
 */
class LaravelExceptionAnalyzer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LaravelExceptionAnalyzer\LaravelExceptionAnalyzer::class;
    }

    public static function handles(?Exceptions $exceptions = null): void
    {
        $reportable = static function (\Throwable $exception): ?ReportClient {
            $config = config('LaravelExceptionAnalyzer');

            if(($config['isEnabled'] ?? false) === false) {
                return null;
            }

            $reportClient = app(ReportClient::class);

            $reportClient->report($exception);

            return $reportClient;
        };

        if ($exceptions) {
            $exceptions->reportable($reportable);

            return;
        }

        $handler = app(ExceptionHandler::class);

        if (method_exists($handler, 'reportable')) {
            $handler->reportable($reportable);
        }

    }
}
