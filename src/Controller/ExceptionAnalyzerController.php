<?php

namespace LaravelExceptionAnalyzer\Controller;

use LaravelExceptionAnalyzer\Models\RepetitiveExceptionModel;
use LaravelExceptionAnalyzer\Models\StructuredExceptionModel;

class ExceptionAnalyzerController
{
    private array $repetitiveFields = [
        'concrete_error_message',
        'full_readable_error_message',
        'is_internal',
        'severity',
        'affected_carrier',
    ];

    public function analyze(): void
    {
        $data = StructuredExceptionModel::where('created_at', '>',
            now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',5)))
            ->where('repetitive_exception_id', null)
            ->get()
            ->toArray();
        $exceptions = $this->countOccurrencesByKey($data, 'cfl');
        foreach ($exceptions as $cfl => $count) {
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

                if (!$repetitiveException) {
                    $repetitiveExceptionData = [];

                    foreach ($this->repetitiveFields as $field) {
                        $data = $this->countOccurrencesByKey($structuredExceptions, $field);
                        arsort($data);
                        $repetitiveExceptionData[$field] = array_key_first($data);
                    }

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

                    app(SlackController::class)
                        ->sendRepetitiveExceptionToSlack($repetitiveException);

                } else {
                    $repetitiveException->increment('occurrence_count', $count);
                }
                // Update all refences for structured exceptions to point to repetitive exception
                StructuredExceptionModel::where('cfl', $cfl)
                    ->whereNull('repetitive_exception_id')
                    ->update(['repetitive_exception_id' => $repetitiveException->id]);
            }
        }
    }

    public function resolveRepetitiveExceptions(): void
    {
        $activeExceptions = RepetitiveExceptionModel::where('is_solved', false)->get();

        foreach ($activeExceptions as $exception) {
            $structuredException = StructuredExceptionModel::where('cfl', $exception->cfl)
                ->where('created_at', '>',
                    now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',5000))
                )->where('repetitive_exception_id', null)
                ->first();
            if (!$structuredException && $exception->updated_at < now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',30))) {
                $exception->is_solved = true;
                $exception->save();
            }
        }
    }

    private function countOccurrencesByKey(array $data, string $value): array
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

}

