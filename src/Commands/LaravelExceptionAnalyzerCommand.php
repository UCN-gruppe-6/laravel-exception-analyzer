<?php

namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;

class LaravelExceptionAnalyzerCommand extends Command
{
    public $signature = 'laravel-exception-analyzer';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
