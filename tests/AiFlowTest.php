<?php

/**
 * Ai flow test
 *
 * This is an integration-style package test.
 *
 * It checks that our exception analyzer pipeline can run end-to-end:
 * - Analyzer is enabled via config
 * - An exception is reported
 * - The code tries to call the AI endpoint
 * - And we can confirm the outgoing HTTP request contains the expected data
 *
 * We fake the AI endpoint so:
 * - no real network call is made
 * - the test is stable and fast
 * - the response is predictable
 */

namespace NikolajVE\LaravelExceptionAnalyzer\Tests;

use Illuminate\Support\Facades\Http;
use LaravelExceptionAnalyzer\AI\AiClient;
use NikolajVE\LaravelExceptionAnalyzer\LaravelExceptionAnalyzer;
use Throwable;

it('classifies an exception via AI and goes through the full flow', function () {
    /**
     * 1. Turn the analyzer on for this test
     * In real usage this would come from .env, but tests should not depend on that.
     * So we set config values directly here.
     */
    config()->set('laravel-exception-analyzer.isEnabled', true);
    /**
     * Configure AI settings for this test run.
     * We point to a fake URL and provide a fake key, because we are not calling a real AI service.
     */
    config()->set('laravel-exception-analyzer.ai', [
        'enabled'  => true,
        'api_key'  => 'test-key-123',
        'endpoint' => 'https://example.test/ai',
        'timeout'  => 5,
    ]);

    /**
     * 2. Fake the AI response
     * Any HTTP request to the endpoint below will return this response.
     * This simulates "the AI answered successfully".
     */
    Http::fake([
        'https://example.test/ai' => Http::response([
            'category'       => 'database',
            'source'         => 'mysql',
            'severity'       => 'high',
            'status_message' => 'Deadlock detected',
        ], 200),
    ]);

    /**
     * 3. Create a test exception
     * This simulates a real exception that the Laravel app would report.
     */
    $exception = new class('Test exception') extends \Exception {};

    /**
     * 4. Resolve the analyzer service from the container
     * This verifies that the package service provider + bindings are working.
     * We are not manually new-ing the analyzer, we get the real one.
     *
     * @var LaravelExceptionAnalyzer $analyzer
     */
    $analyzer = app(LaravelExceptionAnalyzer::class);

    /**
     * 5. Report the exception
     * This should go through the full flow:
     * LaravelExceptionAnalyzer -> ReportClient -> AiClient -> HTTP call (fake)
     *
     * Because Http::fake() is active, the HTTP call is intercepted
     * and returns the fake response instead of leaving the test environment.
     */
    $analyzer->report($exception);

    /**
     * 6. Assert that we actually sent the request we expected and that the HTTP call was made correctly
     *
     * This checks the most important thing:
     * - did we call the correct endpoint?
     * - did we include the exception message in the payload?
     */
    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.test/ai'
            && $request['exception']['message'] === 'Test exception';
    });
});
