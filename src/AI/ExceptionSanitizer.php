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
    /**
     * Sanitize an exception into a safe, structured array.
     * Current fields include:
     * - message: A human-readable explanation of the error.
     * - code: Optional numeric error code.
     * - class: The fully qualified class name of the exception.
     * - file: File path where the exception was thrown.
     * - line: Line number where it occurred.
     *
     * This provides the AI with enough context to classify the exception
     * without exposing internal request data or personal information.
     */
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
