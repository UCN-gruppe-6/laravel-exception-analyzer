<?php
    /**
     * Create repetitive exceptions table
     *
     * This migration introduces the concept of "repetitive exceptions".
     * In our system, many exceptions are not one-off errors.
     * The same underlying problem can occur again and again.
     *
     * Instead of treating every occurrence as a completely new issue,
     * we group related exceptions into a single repetitive exception.
     *
     * This allows the system to:
     * - track how often the same problem happens
     * - separate "new problems" from known, recurring ones
     * - mark recurring problems as solved once they are handled
     *
     * The structured_exception table is then linked to this new table,
     * so each structured exception can optionally belong to a
     * repetitive exception group.
     */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Apply the migration.
     *
     * This creates a new table for repetitive exceptions
     * and adds a reference to it from structured_exception.
     */
    public function up()
    {
        /**
         * Create the repetitive_exceptions table.
         * Each row represents a recurring problem, not a single occurrence.
         */
        Schema::create('repetitive_exceptions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->string('cfl');
            $table->boolean('is_solved')->default(false);
            $table->string('short_error_message');
            $table->text('detailed_error_message');
            $table->integer('occurrence_count')->default(0);
            $table->boolean('is_internal');
            $table->string('severity');
        });

        /**
         * Extend the structured_exception table.
         *
         * This creates the link between:
         * - individual structured exceptions
         * - their associated repetitive exception (if any)
         *
         * A structured exception may or may not be part of a repetitive exception group.
         */
        Schema::table('structured_exception', function (Blueprint $table) {
            $table->integer('repetitive_exception_id')->nullable();
        });
    }

    /**
     * Reverse the migration.
     *
     * This removes the repetitive_exceptions table
     * and the reference from structured_exception.
     */
    public function down()
    {
        // Remove the repetitive exceptions table
        Schema::dropIfExists('repetitive_exceptions');

        // Remove the reference column from structured_exception
        Schema::dropColumns('structured_exception', [
            'repetitive_exception_id',
        ]);
    }
};
