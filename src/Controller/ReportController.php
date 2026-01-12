<?php
/**
 * Report Controller
 *
 * This controller is responsible for capturing and reporting exceptions
 * that occur in the host application. It transforms Throwable objects
 * into a structured array format suitable for storage or sending to
 * an external reporting service.
 *
 * The controller ensures that all relevant exception data is captured
 * and stored in a database (ExceptionModel) or sent to an external system.
 */
namespace LaravelExceptionAnalyzer\Controller;

use Illuminate\Support\Facades\Auth;
use LaravelExceptionAnalyzer\Clients\ReportClient;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use Throwable;

class ReportController
{
    /**
     * Capture and report a Throwable exception.
     *
     * If an external API is configured (REPORT_EXCEPTIONS_API_URL), the
     * exception is sent to that service via ReportClient.
     * Otherwise, the exception is stored locally in the ExceptionModel database table.
     */
    public function report(Throwable $e): void
    {
        $exception = $this->createExceptionArray($e);

        if (config('REPORT_EXCEPTIONS_API_URL')) {
            // Sending to external reporting service (currently not fully implemented)
            ReportClient::sendToExternalService($exception);
            return;
        }

        // Store exception locally in the database
        ExceptionModel::create(
            $exception
        );
    }

    /**
     * Transform a Throwable exception into a structured associative array.
     *
     * This array contains all relevant information about the exception, including:
     * - Message, type, and code
     * - File and line where the exception occurred
     * - URL and hostname for context
     * - Stack trace
     * - Authenticated user ID and email (if available)
     * - Session ID
     * - Timestamp of occurrence
     */
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
