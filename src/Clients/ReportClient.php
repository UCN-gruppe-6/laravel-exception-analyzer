<?php

/**
 * ReportClient
 *
 * When an exception happens, here is what our system does with it.
 *
 * It always handles the same flow:
 * 1) Take the Throwable and turn it into a simple array we can store/send
 * 2) Store the raw exception in the database (so we don't lose it)
 * 3) Send the exception data to AI for classification
 * 4) Log the AI result (and later: store it)
 *
 * This class acts as the intermediary between the main analyzer service
 *  and the underlying AI classification logic.
 */

namespace LaravelExceptionAnalyzer\Clients;

use Illuminate\Support\Facades\Auth;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use Throwable;
use LaravelExceptionAnalyzer\AI\AiClient;

class ReportClient
{
    /**
     * Report
     *
     * This is the method the rest of the system calls when it wants to report an exception.
     *
     * Input: a Throwable (any exception/error in PHP/Laravel)
     * Output: nothing (it just performs side effects: store + classify + log)
     */
    public static function report(Throwable $e): void
    {
        /**
         * 1. Convert the exception object into a clean array
         * We do this because exceptions are objects with lots of internal stuff
         */
        $exception = self::createExceptionArray($e);

        /**
         * 2. (optional): send to external service
         * This is a placeholder for a future feature.
         * The idea is: if we want to report exceptions to some external API, this is where the switch would happen.
         */
        if (config('REPORT_EXCEPTIONS_API_URL') === false) {
            // Not yet implemented
            self::sendToExternalService($exception);

            return;
        }

        /**
         * 3. Store the raw exception in the database
         * We never want to lose the original exception.
         */
        ExceptionModel::create(
            $exception
        );

        /**
         * 4. Get the AI client and ask it to classify the exception
         * The AI returns structured fields like: carrier, severity, short message etc.
         * This is the step that turns raw technical error data into something our system can work with.
         *
         * @var AiClient $aiClient
         */
        $aiClient = app(AiClient::class);

        $result = $aiClient->classify($exception);

//        if ($result !== null) {
//            logger()->info('AI classified exception', [
//                'exception'      => $exception,
//                'classification' => $result->toArray(),
//            ]);
//        }

        /**
         * 5. Log the AI result (if we got one)
         * This is currently used to verify that classification works.
         */
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

    /**
     * Create Exception Array
     *
     * Takes the exception object and extracts the fields we care about, then returns a plain PHP array.
     * This makes it easy to: insert into the database, send to AI, log it
     *
     * It also adds context that isn't inside the exception itself:
     * - current user, current URL, host/environment, session id
     */
    private static function createExceptionArray(Throwable $exception): array
    {
        // The user is optional (could be guest / not logged in)
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

    /**
     * This method is a stub for sending exception data to an external service.
     */
    private static function sendToExternalService(array $exception): void
    {
        // Implement sending to an external service if needed.
    }
}
