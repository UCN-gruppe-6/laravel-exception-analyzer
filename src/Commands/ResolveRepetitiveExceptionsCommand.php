<?php
    /**
     * Resolve Repetitive Exceptions Command
     *
     * This Artisan command is used to handle the last step in the
     * exception processing pipeline: resolving repetitive exceptions.
     *
     * By the time this command runs:
     * - raw exceptions have already been collected
     * - structured exceptions have already been created
     * - repetitive exceptions have already been identified
     *
     * This command is responsible for:
     * - updating repetitive exceptions (e.g. marking them as solved)
     * - applying resolution logic based on system rules
     *
     * It is designed to run in the background.
     * The actual business logic lives in the controller.
     * This command only triggers that logic.
     */
namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\Controller\ExceptionAnalyzerController;

class ResolveRepetitiveExceptionsCommand extends Command
{
    /**
     * The name used to run the command.
     */
    protected $signature = 'resolve:exceptions';
    protected $description = 'Resolve repetitive exceptions';

    /**
     * Execute the command.
     *
     * This method simply calls the controller method
     * that contains the real resolution logic.
     * It triggers the repetitive exception resolution.
     */
    public function handle(): void
    {
        $controller = app(ExceptionAnalyzerController::class);
        $controller->resolveRepetitiveExceptions();
    }
}
