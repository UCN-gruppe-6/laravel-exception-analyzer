<?php
    /**
     * Exception Analyzer Controller
     *
     * This controller is the "brain" of the recurring-exception pipeline.
     * Our system produces lots of structured exceptions.
     * Many of them are basically the same problem happening repeatedly.
     *
     * This controller's job is to:
     * 1) detect when the same problem repeats often (based on CFL)
     * 2) create or update a "repetitive exception" record for that problem
     * 3) link all matching structured exceptions to that repetitive record
     * 4) mark repetitive exceptions as solved when they stop happening
     *
     * Important:
     * - This is not a web controller for user requests.
     * - It is called by scheduled Artisan commands in the background.
     */
namespace LaravelExceptionAnalyzer\Controller;

use LaravelExceptionAnalyzer\Models\RepetitiveExceptionModel;
use LaravelExceptionAnalyzer\Models\StructuredExceptionModel;

class ExceptionAnalyzerController
{
    /**
     * Fields that are used to determine whether exceptions are repetitive.
     *
     * Only these fields are considered when comparing exceptions.
     */
    private array $repetitiveFields = [
        'concrete_error_message',
        'full_readable_error_message',
        'is_internal',
        'severity',
        'affected_carrier',
    ];

    /**
     * Analyze recent structured exceptions to identify repetitive ones.
     *
     * Steps performed:
     * 1. Fetch all structured exceptions created within a configurable
     *    time window that are not yet linked to a repetitive exception.
     * 2. Group exceptions by their "cfl" (classification).
     * 3. For each group, check if it exceeds the threshold to be considered repetitive.
     * 4. Either create a new repetitive exception or update an existing one.
     * 5. Link all structured exceptions in the group to the repetitive exception.
     * 6. Optionally send Slack notifications for new repetitive exceptions.
     */
    public function analyze(): void
    {
        $data = StructuredExceptionModel::where('created_at', '>',
            now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',5)))
            ->where('repetitive_exception_id', null)
            ->get()
            ->toArray();

        // Count occurrences of each unique CFL (classification) value
        $exceptions = $this->countOccurrencesByKey($data, 'cfl');
        foreach ($exceptions as $cfl => $count) {
            // Check if a repetitive exception already exists for this CFL
            $repetitiveException = RepetitiveExceptionModel::where('cfl', $cfl)
                ->where('is_solved', false)
                ->first();
            if ($count >= config('laravel-exception-analyzer.AMOUNT_OF_EXCEPTIONS_WITH_IN_TIME',5)
            || $repetitiveException) {
                $structuredExceptions = StructuredExceptionModel::where('cfl', $cfl)
                    ->where('created_at', '>',
                        now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',5))
                    )
                    ->whereNull('repetitive_exception_id')
                    ->get()
                    ->toArray();

                // If no repetitive exception exists, create a new one
                if (!$repetitiveException) {
                    $repetitiveExceptionData = [];

                    // Determine the most common value for each repetitive field
                    foreach ($this->repetitiveFields as $field) {
                        $data = $this->countOccurrencesByKey($structuredExceptions, $field);
                        arsort($data);
                        $repetitiveExceptionData[$field] = array_key_first($data);
                    }

                    // Create the new repetitive exception in the database
                    $repetitiveException = RepetitiveExceptionModel::create([
                        'cfl' => $cfl,
                        'is_solved' => false,
                        'short_error_message' => $repetitiveExceptionData['concrete_error_message'],
                        'detailed_error_message' => $repetitiveExceptionData['full_readable_error_message'],
                        'occurrence_count' => $count,
                        'is_internal' => $repetitiveExceptionData['is_internal'],
                        'severity' => $repetitiveExceptionData['severity'],
                        'carrier' => $repetitiveExceptionData['affected_carrier'],
                    ]);

                    // Notify Slack about the new repetitive exception
                    app(SlackController::class)
                        ->sendRepetitiveExceptionToSlack($repetitiveException);

                } else {
                    // Increment occurrence count for an existing repetitive exception
                    $repetitiveException->increment('occurrence_count', $count);
                }
                // Update all refences for structured exceptions to point to repetitive exception
                StructuredExceptionModel::where('cfl', $cfl)
                    ->whereNull('repetitive_exception_id')
                    ->update(['repetitive_exception_id' => $repetitiveException->id]);
            }
        }
    }

    /**
     * Mark repetitive exceptions as resolved if no new occurrences
     * have been recorded within a configurable timeframe.
     */
    public function resolveRepetitiveExceptions(): void
    {
        $activeExceptions = RepetitiveExceptionModel::where('is_solved', false)->get();

        foreach ($activeExceptions as $exception) {
            $structuredException = StructuredExceptionModel::where('cfl', $exception->cfl)
                ->where('created_at', '>',
                    now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',5000))
                )->where('repetitive_exception_id', null)
                ->first();

            // If no new exceptions and last update is older than threshold, mark as solved
            if (!$structuredException && $exception->updated_at < now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',30))) {
                $exception->is_solved = true;
                $exception->save();
            }
        }
    }

    /**
     * Count occurrences of values for a specific key in an array of data.
     *
     * Input:
     * - $data: array of associative arrays
     * - $value: key to count occurrences for
     *
     * Output:
     * - associative array where key = value, value = number of occurrences
     */
    private function countOccurrencesByKey(array $data, string $value): array
    {
        $counts = [];
        foreach ($data as $item) {
            if (isset($counts[$item[$value]])) {
                $counts[$item[$value]]++;
            } else {
                $counts[$item[$value]] = 1;
            }
        }
        return $counts;
    }
}
