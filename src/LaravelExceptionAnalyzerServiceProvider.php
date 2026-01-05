<?php

/**
 * Laravel Exception Analyzer Service Provider model
 *
 * In our system, this provider is what makes the whole package actually work:
 * - it registers config + views + migrations + commands
 * - it registers internal services into Laravel’s container
 * - It hooks our analyzer into Laravel’s exception handler
 *
 * Without this file:
 * - the package wouldn't load its config
 * - artisan wouldn't know about the commands
 * - migrations wouldn't be available
 * - and our analyzer would not receive real exceptions
 */

namespace LaravelExceptionAnalyzer;

use Illuminate\Contracts\Debug\ExceptionHandler;
use LaravelExceptionAnalyzer\Clients\ReportClient;
use LaravelExceptionAnalyzer\Commands\ExceptionAnalyzerCommand;
use LaravelExceptionAnalyzer\Commands\ResolveRepetitiveExceptionsCommand;
use LaravelExceptionAnalyzer\Facades\LaravelExceptionAnalyzer;
use LaravelExceptionAnalyzer\Commands\SlackTestCommand;
use LaravelExceptionAnalyzer\Commands\AIClientCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use LaravelExceptionAnalyzer\Commands\LaravelExceptionAnalyzerCommand;
use LaravelExceptionAnalyzer\AI\ExceptionSanitizer;
use LaravelExceptionAnalyzer\AI\AiClient;

class LaravelExceptionAnalyzerServiceProvider extends PackageServiceProvider
{
    /**
     * Registering Package
     *
     * This is where we tell Laravel's container: "If someone asks for X class, here is how to create it."
     * Register all internal services and bind them into the container.
     *
     *  The order of registration matters:
     *  - ExceptionSanitizer -> used by
     *  - AiClient -> used by
     *  - ReportClient -> used by
     *  - LaravelExceptionAnalyzer (main service)
     */
    public function registeringPackage(): void
    {
        /**
         * 1. Register the ExceptionSanitizer as a singleton
         */
        $this->app->singleton(ExceptionSanitizer::class);


        /**
         * 2. Register the AiClient.
         */
//        $this->app->singleton(AiClient::class, function ($app) {
//            return new AiClient(
//                sanitizer: $app[ExceptionSanitizer::class],
//            );
//        });


        /**
         * 3. Register the ReportClient.
         */
//        $this->app->singleton(ReportClient::class, function ($app) {
//            return new ReportClient(
//                aiClient: $app->make(AiClient::class),
//            );
//        });


        /**
         * 4. Register the main analyzer service.
         */
//        $this->app->singleton(LaravelExceptionAnalyzer::class, function ($app) {
//            return new LaravelExceptionAnalyzer(
//                reportClient: $app->make(ReportClient::class),
//            );
//        });
    }

    /**
     * Configure Package
     *
     * This configures what the package "brings with it":
     * - config file
     * - views
     * - artisan commands
     *
     * Spatie Laravel Package Tools makes this setup cleaner.
     */
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-exception-analyzer')
            // Makes config('laravel-exception-analyzer...') available
            ->hasConfigFile()
            // Allows the package to provide Blade views if needed
            ->hasViews()
            /**
             * Register Artisan commands shipped with the package.
             * In our system these commands are part of the background pipeline:
             * - test commands (SlackTestCommand, AIClientCommand)
             * - pipeline commands (ExceptionAnalyzerCommand, ResolveRepetitiveExceptionsCommand)
             */
            ->hasCommands(
                LaravelExceptionAnalyzerCommand::class,
                SlackTestCommand::class,
                AIClientCommand::class,
                ExceptionAnalyzerCommand::class,
                ResolveRepetitiveExceptionsCommand::class);
    }

    /**
     * Register()
     *
     * Runs early in the boot process.
     * This is where we merge our package config into the app config
     * so config() calls work even if the config file is not published.
     *
     * In our system we merge:
     * - laravel-exception-analyzer.php (our package settings)
     * - prism.php (AI provider settings used by AiClient / Prism)
     */
    public function register(): void
    {
        parent::register();

        // Ensure the package config is merged and available as `config('laravel-exception-analyzer')`
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-exception-analyzer.php',
            'laravel-exception-analyzer'
        );

        // Make Prism config available as config('prism')
        $this->mergeConfigFrom(
            __DIR__ . '/../config/prism.php',
            'prism'
        );
    }

    /**
     * Boot()
     *
     * boot() runs after all providers are registered.
     *
     * This is where we connect the package into the running application:
     * - load migrations
     * - load views
     * - hook the analyzer into Laravel's exception handler
     */
    public function boot(): void
    {
        parent::boot();

        // Allow Laravel to load migrations directly from the package (no publish required)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Optionally load views if you want package views available without publishing
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'exception-analyzer');

        /**
         * Hook our analyzer into Laravel's exception handler.
         * This is what makes real runtime exceptions enter our pipeline.
         *
         * Without this line:
         * - exceptions would still exist in Laravel
         * - but our package would never be told about them
         */
        $handler = app(ExceptionHandler::class);
        LaravelExceptionAnalyzer::handles($handler);
    }
}
