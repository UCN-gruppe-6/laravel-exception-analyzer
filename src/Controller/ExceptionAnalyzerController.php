<?php

namespace LaravelExceptionAnalyzer\Controller;

use LaravelExceptionAnalyzer\Models\RepetitiveExceptionModel;
use LaravelExceptionAnalyzer\Models\StructuredExceptionModel;
use LaravelExceptionAnalyzer\Controller\SlackController;

class ExceptionAnalyzerController
{
    private array $repetitiveFields = [
        'short_error_messages',
        'detailed_error_messages',
        'is_internal',
        'severity',
        'carrier',
    ];

    public function analyze(): void
    {
        $data = StructuredExceptionModel::where('created_at', '>',
            now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',5)))
            ->where('repetitive_exception_id', null)
            ->get()
            ->toArray();
        $exceptions = $this->getExceptions($data, 'cfl');
        foreach ($exceptions as $cfl => $count) {
            $repetitiveException = RepetitiveExceptionModel::where('cfl', $cfl)
                ->where('is_solved', false)
                ->first();
            if ($count >= config('laravel-exception-analyzer.AMOUNT_OF_EXCEPTIONS_WITH_IN_TIME',5)
            || $repetitiveException) {
                // Find all Structured exceptions within config time frame with cfl
                $structuredExceptions = StructuredExceptionModel::where('cfl', $cfl)
                    ->where('created_at', '>',
                        now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES',5))
                    )
                    ->whereNull('repetitive_exception_id')
                    ->get()
                    ->toArray();
                // Combine their short texts, long texts and is_internal and severity using AI


                // Upload repetitive exception to database
                if (!$repetitiveException) {
                    $combinedStructuredExceptions = $this->structuredExceptionCombiner($structuredExceptions);
                    $repetitiveExceptionData = $this->combineStructuredExceptionsToRepetitiveException($combinedStructuredExceptions);
                    $repetitiveException = RepetitiveExceptionModel::create([
                        'cfl' => $cfl,
                        'is_solved' => false,
                        'short_error_message' => $repetitiveExceptionData['short_error_messages'],
                        'detailed_error_message' => $repetitiveExceptionData['detailed_error_messages'],
                        'occurrence_count' => $count,
                        'is_internal' => $repetitiveExceptionData['is_internal'],
                        'severity' => $repetitiveExceptionData['severity'],
                        'carrier' => $repetitiveExceptionData['carrier'],
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

    private function combineStructuredExceptionsToRepetitiveException(array $combinedStructuredExceptions)
    {
        $result = [];
        foreach ($this->repetitiveFields as $field) {
            $repetitiveField = $this->getRepetitiveFieldsCount($combinedStructuredExceptions[$field]);
            arsort($repetitiveField);
            $result[$field] = array_key_first($repetitiveField);
        }
        return $result;
    }

    private function getRepetitiveFieldsCount(array $data): array
    {
        $exceptions = [];
        foreach ($data as $exception) {
            if (isset($exceptions[$exception])) {
                $exceptions[$exception]++;
            } else {
                $exceptions[$exception] = 1;
            }
        }
        return $exceptions;
    }

}

