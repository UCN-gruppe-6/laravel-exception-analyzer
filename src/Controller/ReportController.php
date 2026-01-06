<?php

namespace LaravelExceptionAnalyzer\Controller;

use Illuminate\Support\Facades\Auth;
use LaravelExceptionAnalyzer\Clients\ReportClient;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use Throwable;

class ReportController
{

    public function report(Throwable $e): void
    {
        $exception = $this->createExceptionArray($e);

        if (config('REPORT_EXCEPTIONS_API_URL')) {
            // Not yet implemented
            ReportClient::sendToExternalService($exception);
            return;
        }

        ExceptionModel::create(
            $exception
        );
    }

    private function createExceptionArray(Throwable $exception): array
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

}
