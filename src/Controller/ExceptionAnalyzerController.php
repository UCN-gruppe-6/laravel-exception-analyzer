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

use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\AI\AiClient;
use LaravelExceptionAnalyzer\Models\RepetitiveExceptionModel;
use LaravelExceptionAnalyzer\Models\StructuredExceptionModel;

class ExceptionAnalyzerController
{
    /**
     * Analyze
     *
     * This method looks at recent structured exceptions and tries to find
     * repeated patterns using the "cfl" field: carrier + file + line
     *
     * Flow:
     * 1) Fetch recent structured exceptions that are not already linked to a repetitive exception
     * 2) Count how many times each CFL appears
     * 3) If a CFL hits a threshold (or already has an active repetitive issue), create/update a RepetitiveException
     * 4) Link the structured exceptions to the repetitive issue
     *
     * This is how the system goes from: "100 individual errors" to "1 recurring problem with 100 occurrences"
     */
    public function analyze(): void
    {
        // We need the AI client because we use AI to merge multiple messages
        // into one clean short + long description for the repetitive issue.
        $aiClient = app(AiClient::class);

        /**
         * 1. Get recent structured exceptions that are not yet grouped
         * - we only look within a time window (e.g. last 5 minutes)
         * - we only take those where repetitive_exception_id is null, meaning they haven’t been assigned to a repetitive group yet
         */
        $data = StructuredExceptionModel::where('created_at', '>',
            now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',5)))
            ->where('repetitive_exception_id', null)
            ->get()
            ->toArray();

        /**
         * 2. Count how many exceptions we have per CFL
         * Result becomes something like:
         *   "GLS-CarrierService-142" => 7,
         *   "DAO-LabelService-88" => 2
         */
        $exceptions = $this->getExceptions($data, 'cfl');

        /**
         * 3. For each CFL group, decide if it should become a repetitive issue
         */
        foreach ($exceptions as $cfl => $count) {
            // Check if we already have an active repetitive issue for this CFL
            $repetitiveException = RepetitiveExceptionModel::where('cfl', $cfl)
                ->where('is_solved', false)
                ->first();
            /**
             * We create/update a repetitive exception if:
             * - the count reaches the threshold (e.g. 5 within 5 minutes)
             * or there is already an active repetitive issue for this CFL
             */
            if ($count >= config('laravel-exception-analyzer.AMOUNT_OF_EXCEPTIONS_WITH_IN_TIME',5)
            || $repetitiveException) {

                /**
                 * 4. Fetch all recent structured exceptions for this CFL that are still unassigned.
                 * We will either: combine them into a new repetitive exception
                 * or count them as more occurrences for an existing one
                 */
                $structuredExceptions = StructuredExceptionModel::where('cfl', $cfl)
                    ->where('created_at', '>',
                        now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',5))
                    )
                    ->whereNull('repetitive_exception_id')
                    ->get()
                    ->toArray();

                /**
                 * 5. If no repetitive issue exists yet, create one
                 */
                // Combine their short texts, long texts and is_internal and severity using AI
                if (!$repetitiveException) {
                    // Collect all short/detailed messages etc. into grouped arrays
                    $combinedStructuredExceptions = $this->structuredExceptionCombiner($structuredExceptions);
                    // Ask AI to merge those into one repetitive issue description
                    $repetitiveExceptionData = $aiClient->combineStructuredExceptionsToRepetitiveException($combinedStructuredExceptions);

                    // Upload repetitive exception to database
                    $repetitiveException = RepetitiveExceptionModel::create([
                        'cfl' => $cfl,
                        'is_solved' => false,
                        'short_error_message' => $repetitiveExceptionData['short_error_message'],
                        'detailed_error_message' => $repetitiveExceptionData['detailed_error_message'],
                        'occurrence_count' => $count,
                        'is_internal' => $repetitiveExceptionData['is_internal'],
                        'severity' => $repetitiveExceptionData['severity'],
                        'carrier' => $repetitiveExceptionData['carrier'],
                    ]);
                } else {
                    /**
                     * 6. If it already exists, just increase the occurrence counter
                     * This keeps track of "how often does this problem happen".
                     */
                    $repetitiveException->increment('occurrence_count', $count);
                }

                /**
                 * 7. Link all matching structured exceptions to the repetitive exception
                 * This is the actual “grouping”. After this, those structured exceptions are no longer "unassigned".
                 */
                StructuredExceptionModel::where('cfl', $cfl)
                    ->whereNull('repetitive_exception_id')
                    ->update(['repetitive_exception_id' => $repetitiveException->id]);
            }
        }
    }

    /**
     * resolveRepetitiveExceptions
     *
     * A repetitive exception should be marked as "solved" when it stops happening.
     *
     * Flow:
     * 1) Find all repetitive exceptions that are still active (not solved)
     * 2) For each one, check if any new structured exceptions for its CFL have appeared recently
     * 3) If nothing new has happened for a while, mark the repetitive issue as solved
     *
     * This prevents the system from keeping old issues “open forever”.
     */
    public function resolveRepetitiveExceptions(): void
    {
        // Fetch all active repetitive exceptions
        $activeExceptions = RepetitiveExceptionModel::where('is_solved', false)->get();

        /**
         * Check if a new structured exception has appeared recently for this CFL.
         */
        foreach ($activeExceptions as $exception) {
            $structuredException = StructuredExceptionModel::where('cfl', $exception->cfl)
                ->where('created_at', '>',
                    now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',5000))
                )->where('repetitive_exception_id', null)
                ->first();
            /**
             * If we didn't find any new unassigned structured exceptions
             * and the repetitive exception hasn’t been updated for a while, then we mark it as solved.
             */
            if (!$structuredException && $exception->updated_at < now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',30))) {
                $exception->is_solved = true;
                $exception->save();
            }
        }
    }

    /**
     * getExceptions (Helper method)
     *
     * This method counts how many times a certain key appears in an array of exceptions.
     * Example: if you pass 'cfl', it will count how many exceptions share the same cfl.
     */
    private function getExceptions(array $data, string $value): array
    {
        $exceptions = [];
        foreach ($data as $exception) {
            if (isset($exceptions[$exception[$value]])) {
                $exceptions[$exception[$value]]++;
            } else {
                $exceptions[$exception[$value]] = 1;
            }
        }
        return $exceptions;
    }

    /**
     * Structured Exception Combiner (Helper method)
     *
     * Before we can ask AI to "merge" multiple exceptions, we need to format the input in a clean way.
     *
     * This method takes many structured exceptions and groups their fields into arrays:
     * - all short messages together
     * - all detailed messages together
     * - all severity values together
     * etc.
     *
     * That makes it easier for the AI to look at the whole set and decide:
     * - what the combined short message should be
     * - what the combined detailed message should be
     * - what severity/internal/carrier should be based on majority
     */
    private function structuredExceptionCombiner(array $structuredExceptions): array
    {
        $combinedStructuredExceptions = [];
        foreach ($structuredExceptions as $structuredException) {
            $combinedStructuredExceptions['short_error_messages'][] = $structuredException['concrete_error_message'];
            $combinedStructuredExceptions['detailed_error_messages'][] = $structuredException['full_readable_error_message'];
            $combinedStructuredExceptions['is_internal'][] = $structuredException['is_internal'];
            $combinedStructuredExceptions['severity'][] = $structuredException['severity'];
            $combinedStructuredExceptions['carrier'][] = $structuredException['affected_carrier'];
    }
        return $combinedStructuredExceptions;
    }

}

