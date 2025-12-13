<?php

namespace LaravelExceptionAnalyzer\Controller;

use LaravelExceptionAnalyzer\Models\StructuredExceptionModel;

class ExceptionAnalyzerController
{
    public function analyze(): void
    {
        $data = StructuredExceptionModel::where('created_at', '<',
            now()->subMinute(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES'),5))->get();
        $exceptions = $this->getExceptions($data);
        foreach ($exceptions as $exception) {

        }


    }

    public function getExceptions(array $data): array
    {
        $exceptions = [];
        foreach ($data as $exception) {
            if(isset($exception->cfl) ) {
                $exceptions[$exception->cfl]++;
                continue;
            }
            $exceptions[$exception->cfl] = 1;
        }
        return $exceptions;

    }

}

