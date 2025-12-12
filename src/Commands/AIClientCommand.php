<?php

namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\AI\AiClient;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use LaravelExceptionAnalyzer\Models\StructuredExceptionModel;

class AIClientCommand extends Command
{
    protected $signature = 'send:AI';
    protected $description = 'Send an exception payload to Gemini and print the response';

    public function handle(): void
    {
        $exception = ExceptionModel::select(['id', 'message', 'type', 'code', 'file', 'line', 'url', 'hostname', 'user_id', 'session_id', 'level'])->where('id', 1)->get();

        Log::info($exception);

        $aiClient = app(AiClient::class);

        $response = $aiClient->classify($exception->first()->toArray());

        StructuredExceptionModel::create($response);
    }
}
