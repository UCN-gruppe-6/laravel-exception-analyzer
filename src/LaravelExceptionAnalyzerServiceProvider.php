<?php

namespace LaravelExceptionAnalyzer;

use Illuminate\Contracts\Debug\ExceptionHandler;
use LaravelExceptionAnalyzer\Commands\ExceptionAnalyzerCommand;
use LaravelExceptionAnalyzer\Commands\ResolveRepetitiveExceptionsCommand;
use LaravelExceptionAnalyzer\Facades\LaravelExceptionAnalyzer;
use LaravelExceptionAnalyzer\Commands\SlackTestCommand;
use LaravelExceptionAnalyzer\Commands\AIClientCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use LaravelExceptionAnalyzer\Commands\LaravelExceptionAnalyzerCommand;

/**
 * LaravelExceptionAnalyzerServiceProvider
 *
 * This is the main service provider for the package.
 */
class LaravelExceptionAnalyzerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-exception-analyzer')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommands(
                SlackTestCommand::class,
                AIClientCommand::class,
                ExceptionAnalyzerCommand::class,
                ResolveRepetitiveExceptionsCommand::class);
    }

    public function register(): void
    {
        parent::register();

        // Ensure the package config is merged and available as `config('laravel-exception-analyzer')`
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-exception-analyzer.php',
            'laravel-exception-analyzer'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../config/prism.php',
            'prism'
        );
    }

    public function boot(): void
    {
        parent::boot();

        // Allow Laravel to load migrations directly from the package (no publish required)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $handler = app(ExceptionHandler::class);
        LaravelExceptionAnalyzer::handles($handler);
    }
}
