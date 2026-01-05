<?php

/**
 * Test case
 *
 * This is the base TestCase for the package tests.
 * Because LaravelExceptionAnalyzer is a package (not a normal Laravel app),
 * we need a “mini Laravel app” environment to run tests inside.
 *
 * Orchestra Testbench provides exactly that: It boots a lightweight Laravel application so we can:
 * - load our service provider
 * - access the container (app())
 * - use config(), database, facades, etc.
 *
 * Every package test will extend this class so they all share the same test environment setup.
 */

namespace NikolajVE\LaravelExceptionAnalyzer\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use NikolajVE\LaravelExceptionAnalyzer\LaravelExceptionAnalyzerServiceProvider;

class TestCase extends Orchestra
{
    /**
     * setUp() runs before each test.
     * We use this to configure test-specific behavior for the package.
     */
    protected function setUp(): void
    {
        parent::setUp();

        /**
         * Laravel can auto-guess factory class names for models,
         * but in packages the namespace is different than a normal app.
         *
         * This tells Laravel: "If you need a factory for a model, look in our package factory namespace."
         * This makes factories work normally inside package tests.
         */
        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'NikolajVE\\LaravelExceptionAnalyzer\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    /**
     * This tells Orchestra which service providers to load for the test environment.
     * This is how the package actually gets booted in tests.
     *
     * Without loading the service provider:
     * - config wouldn't be merged
     * - commands and container bindings wouldn't exist
     * - exception hooking wouldn't be registered
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelExceptionAnalyzerServiceProvider::class,
        ];
    }

    /**
     * This method lets us configure the test environment.
     * This is similar to editing a normal Laravel app’s config,
     * but we do it here because the "app" only exists during tests.
     */
    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
