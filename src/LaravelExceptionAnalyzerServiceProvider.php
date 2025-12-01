<?php

namespace NikolajVE\LaravelExceptionAnalyzer;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use NikolajVE\LaravelExceptionAnalyzer\Commands\LaravelExceptionAnalyzerCommand;
use NikolajVE\LaravelExceptionAnalyzer\AI\ExceptionSanitizer;
use NikolajVE\LaravelExceptionAnalyzer\Clients\ReportClient;
use NikolajVE\LaravelExceptionAnalyzer\AI\AiClient;

/**
 * LaravelExceptionAnalyzerServiceProvider
 *
 * This is the main service provider for the package.
 * It registers all required bindings in Laravel’s service container,
 * configures package resources, and ensures that all internal components
 * are wired together correctly.
 *
 * The service provider is loaded automatically via composer.json
 * under the "extra.laravel.providers" section.
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
     *
     * Each binding is set as a singleton so the same instance is reused
     * throughout the lifetime of the application.
     */
    public function registeringPackage():void
    {
        /**
         * 1. Register the ExceptionSanitizer.
         *
         * This is responsible for cleaning and shaping exception data
         * before sending it to the AI service.
         */
        $this->app->singleton(ExceptionSanitizer::class);


        /**
         * 2. Register the AiClient.
         *
         * AiClient depends on the sanitizer and handles all communication
         * with the external AI service.
         */
        $this->app->singleton(AiClient::class, function ($app) {
            return new AiClient(
                sanitizer: $app[ExceptionSanitizer::class],
            );
        });


        /**
         * 3. Register the ReportClient.
         *
         * ReportClient uses the AiClient to classify incoming exceptions
         * and will later also be responsible for saving the results
         * into the database.
         */
        $this->app->singleton(ReportClient::class, function ($app) {
            return new ReportClient(
                aiClient: $app->make(AiClient::class),
            );
        });


        /**
         * 4. Register the main analyzer service.
         *
         * This is the class that the facade points to via getFacadeAccessor().
         * It ties the entire reporting process together.
         */
        $this->app->singleton(LaravelExceptionAnalyzer::class, function ($app) {
            return new LaravelExceptionAnalyzer(
                reportClient: $app->make(ReportClient::class),
            );
        });
    }

    /**
     * Configure package metadata using Spatie’s LaravelPackageTools.
     *
     * This method defines:
     * - the package name
     * - config file publishing
     * - view publishing
     * - database migration publishing
     * - package console commands
     *
     * This enables a clean and standardized setup for Laravel packages.
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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_exception_analyzer_table')
            ->hasCommand(LaravelExceptionAnalyzerCommand::class);
    }
}
