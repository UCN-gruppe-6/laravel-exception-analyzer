<?php

namespace LaravelExceptionAnalyzer\Clients;

use Illuminate\Support\Facades\Auth;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use Throwable;
use LaravelExceptionAnalyzer\AI\AiClient;

/**
 * ReportClient
 *
 * Coordinates the process of handling an exception by:
 * 1. Calling the AI service to classify the exception.
 * 2. (In the future) Persisting the result into the database.
 *
 * This class acts as the intermediary between the main analyzer service
 * and the underlying AI classification logic.
 */
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

        /** @var AiClient $aiClient */
        $aiClient = app(AiClient::class);

        $result = $aiClient->classify($exception);

//        if ($result !== null) {
//            logger()->info('AI classified exception', [
//                'exception'      => $exception,
//                'classification' => $result->toArray(),
//            ]);
//        }

        if ($result !== null) {

            $classification = is_array($result)
                ? $result
                : (method_exists($result, 'toArray') ? $result->toArray() : (array) $result);

            logger()->info('AI classified exception', [
                'exception'      => $exception,
                'classification' => $classification,
            ]);
        }
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
