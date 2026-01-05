<?php

/**
 * Create Structured Exception Table
 *
 * This migration creates the "structured_exception" table.
 *
 * This table does NOT store raw exceptions.
 * Instead, it stores a cleaned, structured, and human-readable
 * version of exceptions that have already been collected.
 *
 * In our system, this table represents the next step AFTER
 * raw exception data has been captured.
 *
 * Raw exceptions can be noisy and technical.
 * This table exists so exceptions can be:
 * - categorized
 * - simplified
 * - linked to business context (carrier, user, severity)
 * - shown clearly in the frontend
 *
 * You can think of this table as the "processed" version
 * of an exception, derived from the raw exception table.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     *
     * This method defines which structured data we store
     * for each processed exception.
     */
    public function up()
    {
        Schema::create('structured_exception', function (Blueprint $table) {
            $table->id();
            $table->integer('exception_id');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->integer('user_id')->nullable();
            $table->string('affected_carrier')->nullable();
            $table->boolean('is_internal');
            $table->string('severity');
            $table->string('concrete_error_message');
            $table->text('full_readable_error_message')->comment('readable for a dummy');

        });
    }

    /**
     * Reverse the migration.
     *
     * Drops the table if the migration is rolled back.
     */
    public function down()
    {
        Schema::dropIfExists('structured_exception');
    }
};

