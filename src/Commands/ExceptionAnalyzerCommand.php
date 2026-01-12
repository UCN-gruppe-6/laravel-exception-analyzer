<?php
/**
 * Exception Analyzer Command
 *
 * This Artisan command is used to trigger exception analysis
 * from the command line or the scheduler.
 *
 * In our system, exception analysis is part of a background pipeline.
 *
 * This command acts as a clean entry point that:
 * - can be run manually during development
 * - can be scheduled to run automatically
 *
 * The actual analysis logic lives in the controller.
 * This command only triggers it.
 */
namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\Controller\ExceptionAnalyzerController;

class ExceptionAnalyzerCommand extends Command
{
    /**
     * The name used to run the command.
     * Example: php artisan Analyze:Exception
     */
    protected $signature = 'Analyze:Exception';
    protected $description = 'Analyze Exceptions';

    /**
     * Execute the command.
     *
     * This method is called when the command runs.
     */
    public function handle(): void
    {
        /**
         * Resolve the ExceptionAnalyzerController from the container
         * and call its analyze() method.
         *
         * The controller contains the real business logic.
         * The command simply acts as a trigger.
         */
        $controller = app(ExceptionAnalyzerController::class);
        $controller->analyze();
    }
}
