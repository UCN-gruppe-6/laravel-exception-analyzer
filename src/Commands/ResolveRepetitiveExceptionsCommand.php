<?php

namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\Controller\ExceptionAnalyzerController;

class ResolveRepetitiveExceptionsCommand extends Command
{
    protected $signature = 'resolve:exceptions';
    protected $description = 'Resolve repetitive exceptions';

    public function handle(): void
    {
        $controller = app(ExceptionAnalyzerController::class);
        $controller->resolveRepetitiveExceptions();
    }
}
