<?php

namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use LaravelExceptionAnalyzer\Controller\SlackController;

class SlackTestCommand extends Command
{
    public $signature = 'slack-test';

    public $description = 'Command for testing Slack integration';

    public function handle(): void
    {
        $slackController = app(SlackController::class);
        $slackController->sendMessageToSlack("This is a test exception message from SlackTestCommand.", "This is a test AI analysis message from SlackTestCommand.");
    }
}
