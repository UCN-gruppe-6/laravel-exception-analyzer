<?php
/**
 * Add cfl to structured exceptions table
 *
 * This migration adds the "cfl" column to the structured_exception table.
 * The cfl value is used as an additional identifier or reference
 * that comes from the exception source or processing logic.
 *
 * In our system, this field is needed to:
 * - carry extra context from the original exception
 * - support correlation or grouping of related exceptions
 * - make it easier to trace or reference exceptions later
 *
 * It is added to the structured table because it belongs to the
 * processed, business-relevant representation of an exception.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Apply the migration.
     *
     * Adds the "cfl" column to the structured_exception table
     * so the value can be stored alongside other processed
     * exception information.
     */
    public function up()
    {
        Schema::table('structured_exception', function (Blueprint $table) {
            $table->string('cfl');
        });
    }

    /**
     * Reverse the migration.
     *
     * Removes the "cfl" column again if the migration is rolled back.
     */
    public function down()
    {
        Schema::dropColumns('structured_exception', [
            'cfl',
        ]);
    }
};

