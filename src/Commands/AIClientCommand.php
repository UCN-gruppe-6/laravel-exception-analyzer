<?php
    /**
     * AI Client Command
     *
     * This command is responsible for triggering the AI analysis flow.
     * It acts as an entry point that can be executed manually or automatically
     * via the Laravel Scheduler.
     *
     * The command itself contains no business logic.
     * Its only responsibility is to delegate the work to the AIController.
     */
namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use LaravelExceptionAnalyzer\Controller\AIController;

class AIClientCommand extends Command
{
    // This is the name used when executing the command
    protected $signature = 'send:AI';
    // A short description of what the command does.
    protected $description = 'Send an exception payload to AI and save the response';

    /**
     * Execute the command.
     *
     * This method is called when the command runs.
     */
    public function handle(): void
    {
        $controller = app(AIController::class);
        $controller->analyzeExceptions();
    }
}
