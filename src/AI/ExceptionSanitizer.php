<?php


namespace LaravelExceptionAnalyzer\AI;

/**
 * ExceptionSanitizer
 *
 * Responsible for extracting and cleaning exception data before it is sent
 * to the external AI service. This ensures that only safe, non-sensitive,
 * and relevant information leaves the application.
 */
class ExceptionSanitizer
{
    public static function sanitize(array $exception): array
    {
        return [
            'message' => $exception['message'] ?? null,
            'type'    => $exception['type'] ?? null,
            'code'    => $exception['code'] ?? null,
            'file'     => $exception['file'] ?? null,
            'line'     => $exception['line'] ?? null,
        ];
    }
}
