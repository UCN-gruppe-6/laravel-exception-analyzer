<?php

/**
 * Laravel Exception Analyzer Command
 *
 * This is the base / placeholder Artisan command for the LaravelExceptionAnalyzer package.
 *
 * Its main purpose right now is:
 * - to verify that the package is correctly installed
 * - to confirm that Artisan can discover and run package commands
 *
 * It does not perform any exception analysis or processing.
 *
 * You can think of this command as a simple "health check"
 * or entry point for future package-level commands.
 */

namespace LaravelExceptionAnalyzer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LaravelExceptionAnalyzer\Enums\Carrier;

class LaravelExceptionAnalyzerCommand extends Command
{
    /**
     * The name used to run the command.
     * Example: php artisan laravel-exception-analyzer
     */
    public $signature = 'laravel-exception-analyzer';

    public $description = 'My command';

    /**
     * Execute the command.
     *
     * Right now, this method only prints a confirmation message.
     * If this message appears, we know:
     * - the package is loaded
     * - the command is registered
     * - Artisan is working as expected
     */
    public function handle(): int
    {
        $this->comment('All done');

        // Return SUCCESS to indicate the command finished without errors
        return self::SUCCESS;
    }
}
