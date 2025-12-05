<?php

namespace LaravelExceptionAnalyzer;

use Illuminate\Contracts\Debug\ExceptionHandler;
use LaravelExceptionAnalyzer\Facades\LaravelExceptionAnalyzer;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use LaravelExceptionAnalyzer\Commands\LaravelExceptionAnalyzerCommand;
use LaravelExceptionAnalyzer\AI\ExceptionSanitizer;
use NikolajVE\LaravelExceptionAnalyzer\Clients\ReportClient;
use LaravelExceptionAnalyzer\AI\AiClient;

/**
 * LaravelExceptionAnalyzerServiceProvider
 *
 * This is the main service provider for the package.
 */
class LaravelExceptionAnalyzerServiceProvider extends PackageServiceProvider
{
    /**
     * Register all internal services and bind them into the container.
     *
     * The order of registration matters:
     * - ExceptionSanitizer → used by
     * - AiClient → used by
     * - ReportClient → used by
     * - LaravelExceptionAnalyzer (main service)
     */
    public function registeringPackage():void
    {
        /**
         * 1. Register the ExceptionSanitizer.
         */
        $this->app->singleton(ExceptionSanitizer::class);


        /**
         * 2. Register the AiClient.
         */
        $this->app->singleton(AiClient::class, function ($app) {
            return new AiClient(
                sanitizer: $app[ExceptionSanitizer::class],
            );
        });


        /**
         * 3. Register the ReportClient.
         */
        $this->app->singleton(ReportClient::class, function ($app) {
            return new ReportClient(
                aiClient: $app->make(AiClient::class),
            );
        });


        /**
         * 4. Register the main analyzer service.
         */
        $this->app->singleton(LaravelExceptionAnalyzer::class, function ($app) {
            return new LaravelExceptionAnalyzer(
                reportClient: $app->make(ReportClient::class),
            );
        });
    }
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
            ->hasMigration('create_exception_analyzer_table')
            ->hasCommand(LaravelExceptionAnalyzerCommand::class);
    }

    public function register(): void
    {
        parent::register();

        // Ensure the package config is merged and available as `config('laravel-exception-analyzer')`
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-exception-analyzer.php',
            'laravel-exception-analyzer'
        );
    }

    public function boot(): void
    {
        parent::boot();

        // Publish the entire migrations directory so vendor:publish copies real migration files
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        // Allow Laravel to load migrations directly from the package (no publish required)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Optionally load views if you want package views available without publishing
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'exception-analyzer');

        $handler = app(ExceptionHandler::class);
        LaravelExceptionAnalyzer::handles($handler);
    }
}
