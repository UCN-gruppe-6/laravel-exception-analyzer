<?php

namespace NikolajVE\LaravelExceptionAnalyzer;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use NikolajVE\LaravelExceptionAnalyzer\Commands\LaravelExceptionAnalyzerCommand;

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
            ->hasMigration('create_laravel_exception_analyzer_table')
            ->hasCommand(LaravelExceptionAnalyzerCommand::class);
    }
}
