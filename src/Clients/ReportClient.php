<?php

namespace LaravelExceptionAnalyzer\Clients;

use Illuminate\Support\Facades\Auth;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use Throwable;

class ReportClient
{
    public static function report(Throwable $e): void
    {
        $exception = self::createExceptionArray($e);

        if (config('REPORT_EXCEPTIONS_API_URL') === false) {
            // Not yet implemented
            self::sendToExternalService($exception);

            return;
        }

        ExceptionModel::create(
            $exception
        );
    }

    private static function createExceptionArray(Throwable $exception): array
    {
        $user = Auth::user();

        return [
            'message' => $exception->getMessage(),
            'type' => get_class($exception),
            'code' => (string)$exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => request()->fullUrl() ?? null,
            'hostname' => gethostname() ?: 'unknown',
            'stack_trace' => $exception->getTraceAsString(),
            'user_id' => $user?->id ?? null,
            'user_email' => $user?->email ?? null,
            'session_id' => session()->getId() ?? null,
            'level' => '', // #TODO: Determine how to set the level
            'created_at' => now(),
        ];
    }

    private static function sendToExternalService(array $exception): void
    {
        // Implement sending to an external service if needed.
    }
}
