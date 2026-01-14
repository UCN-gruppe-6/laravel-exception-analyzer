<?php
    /**
     * Slack test command
     *
     * This Artisan command exists purely to test the Slack integration.
     *
     * It allows us to manually trigger a Slack message from the terminal to verify that:
     * - the Slack webhook URL is configured correctly
     * - the SlackController works as expected
     * - messages actually arrive in the Slack channel
     *
     * This command is not part of the exception processing pipeline.
     */
namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use LaravelExceptionAnalyzer\Controller\SlackController;
use LaravelExceptionAnalyzer\Models\RepetitiveExceptionModel;

class SlackTestCommand extends Command
{
    // The name used to run the command.
    public $signature = 'slack-test';

    public $description = 'Command for testing Slack integration';

    /**
     * Execute the command.
     *
     * When this runs, it sends a hardcoded test message to Slack.
     * If the message appears, the integration is working.
     */
    public function handle(): void
    {
        $exception = RepetitiveExceptionModel::where('id', 1)->first();
        $controller = app(SlackController::class);
        $controller->sendRepetitiveExceptionToSlack( $exception );
    }
}
