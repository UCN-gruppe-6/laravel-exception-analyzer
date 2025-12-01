<?php

namespace NikolajVE\LaravelExceptionAnalyzer\Tests;

use Illuminate\Support\Facades\Http;
use NikolajVE\LaravelExceptionAnalyzer\AI\AiClient;
use NikolajVE\LaravelExceptionAnalyzer\LaravelExceptionAnalyzer;
use Throwable;

it('classifies an exception via AI and goes through the full flow', function () {
    // 1) Sæt config direkte (ingen .env nødvendig)
    config()->set('laravel-exception-analyzer.isEnabled', true);
    config()->set('laravel-exception-analyzer.ai', [
        'enabled'  => true,
        'api_key'  => 'test-key-123',
        'endpoint' => 'https://example.test/ai',
        'timeout'  => 5,
    ]);

    // 2) Fake AI-svaret
    Http::fake([
        'https://example.test/ai' => Http::response([
            'category'       => 'database',
            'source'         => 'mysql',
            'severity'       => 'high',
            'status_message' => 'Deadlock detected',
        ], 200),
    ]);

    // 3) Lav en test-exception
    $exception = new class('Test exception') extends \Exception {};

    // 4) Resolve din hovedservice fra containeren
    /** @var LaravelExceptionAnalyzer $analyzer */
    $analyzer = app(LaravelExceptionAnalyzer::class);

    // 5) Kald report() – det går gennem:
    // LaravelExceptionAnalyzer → ReportClient → AiClient → Http::fake()
    $analyzer->report($exception);

    // 6) Assert at HTTP-kaldet rent faktisk blev lavet korrekt
    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.test/ai'
            && $request['exception']['message'] === 'Test exception';
    });
});
