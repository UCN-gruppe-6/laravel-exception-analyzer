<?php

/**
 * Create exception analyzer table
 *
 * This migration creates the "exceptions" table.
 * This table stores RAW exception data exactly as it occurs
 * in the application.
 *
 * It is meant to capture:
 * - what went wrong
 * - where it went wrong
 * - who was affected
 * - and under which conditions
 *
 * This table represents the lowest level of error storage
 * in the system.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     *
     * This method creates the database table and defines
     * which data we store for each exception.
     */
    public function up()
    {
        Schema::create('exceptions', function (Blueprint $table) {
            $table->id();
            $table->text('message')->comment('Error message of the exception');
            $table->string('type')->comment('Type of exception, e.g., ErrorException, ModelNotFoundException');
            $table->string('code')->comment('Error code associated with the exception');
            $table->string('file')->comment('File where the exception occurred');
            $table->integer('line')->comment('Line number in the file where the exception occurred');
            $table->string('url')->nullable()->comment('URL where the exception occurred');
            $table->string('hostname')->comment('Hostname of the server where the exception occurred, e.g., production, staging, testing');
            $table->text('stack_trace')->comment('Full stack trace of the exception');
            $table->integer('user_id')->nullable()->comment('ID of the user affected by the exception, if applicable');
            $table->string('user_email')->nullable()->comment('Email of the user affected by the exception, if applicable');
            $table->string('session_id')->nullable()->comment('Session ID of the session affected by the exception, if applicable');
            $table->timestamp('created_at', 2)->comment('Timestamp for when the exception was created');
            $table->string('level')->comment('Severity level of the exception, e.g., error, warning, info');
        });
    }

    /**
     * Reverse the migration.
     *
     * This drops the table if the migration is rolled back.
     */
    public function down()
    {
        Schema::dropIfExists('exceptions');
    }
};
