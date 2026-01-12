<?php
    /**
     * Laravel Exception Analyzer Service Provider
     *
     * This service provider is the central piece that makes the Laravel Exception Analyzer package work.
     * It is responsible for:
     * - Registering the package's configuration, views, migrations, and commands
     * - Registering internal services into Laravel's service container
     * - Hooking the analyzer into Laravel's global exception handler
     *
     * Without this service provider:
     * - The package's config wouldn't be loaded
     * - Artisan wouldn't recognize the package commands
     * - Migrations wouldn't be available
     * - Exceptions from the host application would not be captured and analyzed
     */
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
    /**
     * Configure the package using Spatie's PackageTools.
     * This registers the package name, config files, views, and commands.
     */
    public function configurePackage(Package $package): void
    {
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

    /**
     * Register package services.
     *
     * Ensures that package configuration is merged into Laravel's config system
     * so that it can be accessed via `config('laravel-exception-analyzer')`.
     */
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

    /**
     * Boot the package.
     *
     * Loads migrations directly from the package so developers don't need to publish them.
     * Also hooks the package into Laravel's global exception handler using the facade.
     */
    public function boot(): void
    {
        parent::boot();

        // Allow Laravel to load migrations directly from the package (no publish required)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $handler = app(ExceptionHandler::class);
        LaravelExceptionAnalyzer::handles($handler);
    }
}
