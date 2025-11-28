<?php

namespace LaravelExceptionAnalyzer\Clients;

use Illuminate\Support\Facades\Auth;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use Throwable;

class ReportClient
{
    public static function report(Throwable $e): void
    {
        $user = Auth::user();

        $exception = [
            'message' => $e->getMessage(),
            'type' => get_class($e),
            'code' => (string)$e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => request()->fullUrl() ?? null,
            'hostname' => gethostname() ?: 'unknown',
            'stack_trace' => $e->getTraceAsString(),
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'session_id' => session()->getId() ?? null,
            'level' => '', // #TODO: Determine how to set the level
        ];

        if (config('REPORT_EXCEPTIONS_API_URL') === false) {
            // If you wish to send it to an external service, implement that here.
            return;
        }

        ExceptionModel::create(
            $exception
        );
    }
}
