<?php

namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\AI\AiClient;
use LaravelExceptionAnalyzer\Controller\ExceptionAnalyzerController;
use LaravelExceptionAnalyzer\Models\ExceptionModel;
use LaravelExceptionAnalyzer\Models\StructuredExceptionModel;

class ExceptionAnalyzerCommand extends Command
{
protected $signature = 'Analyze:Exception';
protected $description = 'Analyze Exceptions';

public function handle(): void
{
$controller = app(ExceptionAnalyzerController::class);
$controller->analyze();


}
}
