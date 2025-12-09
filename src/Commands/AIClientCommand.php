<?php

namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\AI\AiClient;
use LaravelExceptionAnalyzer\Models\ExceptionModel;

class AIClientCommand extends Command
{
    protected $signature = 'send:gemini';
    protected $description = 'Send an exception payload to Gemini and print the response';

    public function handle(): void
    {
//        $msg = $this->argument('message') ?: 'Simulated exception for testing Gemini integration';
//
//        try {
//            // simulate throwing to get realistic stack trace
//            throw new \Exception($msg);
//        } catch (Throwable $e) {
//            $payload = [
//                // adapt this payload to the Gemini schema you use
//                'input' => [
//                    'text' => "Exception message: " . $e->getMessage() . "\n\nStack trace:\n" . $e->getTraceAsString()
//                ],
//                'metadata' => [
//                    'php_version' => PHP_VERSION,
//                    'app_env' => env('APP_ENV'),
//                ],
//            ];
//
//            $endpoint = env('GEMINI_ENDPOINT');
//            $apiKey = env('GEMINI_API_KEY');
//
//            if (! $endpoint || ! $apiKey) {
//                $this->error('GEMINI_ENDPOINT or GEMINI_API_KEY not set in .env');
//                return 1;
//            }
//
//            $response = Http::withToken($apiKey)
//                ->acceptJson()
//                ->post($endpoint, $payload);
//
//            if ($response->successful()) {
//                $this->info('Gemini response:');
//                $this->line($response->body());
//                return 0;
//            }
//
//            $this->error('Request failed: ' . $response->status());
//            $this->line($response->body());
//            return 2;

        $exception = ExceptionModel::where('id', 1)->limit(1)->get();

        $aiClient = app(AiClient::class);

        $response = $aiClient->classify($exception->first()->toArray());

    }
}
