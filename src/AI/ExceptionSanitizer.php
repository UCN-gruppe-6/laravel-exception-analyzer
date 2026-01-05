<?php

/**
 * ExceptionSanitizer
 *
 * This class exists to control what exception data is allowed
 * to leave the application when we send something to an AI service.
 *
 * This ensures that only safe, non-sensitive and relevant information leaves the application.
 */

namespace LaravelExceptionAnalyzer\AI;

class ExceptionSanitizer
{
    /**
     * Takes a raw exception array and extracts only the fields
     * that the AI needs to understand the problem.
     *
     * Everything else is intentionally left out.
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
