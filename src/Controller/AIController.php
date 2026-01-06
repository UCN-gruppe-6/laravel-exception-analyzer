<?php

namespace LaravelExceptionAnalyzer\Controller;

use Illuminate\Support\Carbon;
use LaravelExceptionAnalyzer\Clients\AIClient;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use LaravelExceptionAnalyzer\Models\StructuredExceptionModel;

class AIController
{
    public function analyzeExceptions(): void
    {
        $exceptions =
            ExceptionModel::select(['id', 'message', 'type', 'code', 'file', 'line', 'url', 'hostname', 'user_id', 'session_id', 'level'])
                ->where('created_at', '>', Carbon::now()->subMinutes(config('laravel-exception-analyzer.CHECK_EXCEPTION_WITH_IN_MINUTES')))
                ->get();

        $aiClient = app(AIClient::class);
        foreach ($exceptions as $exception) {
            $response = $aiClient->classify($exception->toArray());
            StructuredExceptionModel::create($response);
        }
    }

}
