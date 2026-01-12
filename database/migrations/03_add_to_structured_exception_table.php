<?php
    /**
     * Add to structured exception table
     *
     * This migration adjusts existing tables to better support
     * how exceptions are analyzed and displayed in the system.
     *
     * Two things happen here:
     * 1) The structured_exception table is extended with additional
     *    technical context (file name, line number, error code).
     * 2) The message column in the raw exceptions table is changed
     *    to allow longer error messages.
     *
     * These changes reflect a shift from a minimal setup to a more
     * detailed and useful exception analysis pipeline.
     */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Apply the migration.
     *
     * This method updates existing tables to store
     * additional information needed by the system.
     */
    public function up()
    {
        /**
         * Extend the structured_exception table.
         *
         * These fields make it possible to:
         * - show where the error originated (file + line)
         * - display or reference the original error code
         *
         * This adds useful technical context without falling back to the raw exception table.
         */
        Schema::table('structured_exception', function (Blueprint $table) {
            $table->string('file_name');
            $table->string('line_number');
            $table->string('code');
        });

        /**
         * Update the raw exceptions table.
         *
         * Some exception messages turned out to be longer than originally expected.
         * Changing the column to TEXT ensures we do not lose information due to length limits.
         */
        Schema::table('exceptions', function (Blueprint $table) {
            $table->text('message')->change();
        });
    }

    /**
     * Reverse the migration.
     *
     * This removes the added columns from structured_exception
     * and restores the original column type for exceptions.message.
     */
    public function down()
    {
        // Remove the additional context fields again
        Schema::dropColumns('structured_exception', [
            'file_name',
            'line_number',
            'code',
        ]);

        // Restore the original message column type
        Schema::table('exceptions', function (Blueprint $table) {
            $table->string('message')->change();
        });
    }
};

