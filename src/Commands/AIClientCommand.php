<?php

namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use LaravelExceptionAnalyzer\Controller\AIController;

class AIClientCommand extends Command
{
    protected $signature = 'send:AI';
    protected $description = 'Send an exception payload to AI and save the response';

    public function handle(): void
    {
        $controller = app(AIController::class);
        $controller->analyzeExceptions();
    }
}
