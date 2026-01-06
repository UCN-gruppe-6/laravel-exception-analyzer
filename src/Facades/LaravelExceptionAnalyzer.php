<?php

namespace LaravelExceptionAnalyzer\Facades;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Facade;
use LaravelExceptionAnalyzer\Controller\ReportController;

/**
 * @see LaravelExceptionAnalyzer
 */
class LaravelExceptionAnalyzer extends Facade
{

    public static function handles(?ExceptionHandler $exceptions = null): void
    {
        $reportable = static function (\Throwable $exception): ?ReportController {
            $config = config('laravel-exception-analyzer', []);

            if(($config['isEnabled'] ?? false) === false) {
                return null;
            }

            $controller = app(ReportController::class);

            $controller->report($exception);

            return $controller;
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
