<?php

/**
 * Add carrier to repetitive exceptions table
 *
 * This migration adds a "carrier" field to the repetitive_exceptions table.
 *
 * Repetitive exceptions represent recurring problems, not single errors.
 * In practice, many of these recurring problems are tied to a specific
 * carrier (for example GLS, PostNord, DAO, etc.).
 *
 * Adding the carrier directly to the repetitive exception makes it possible to:
 * - see which carrier a recurring problem belongs to at a glance
 * - filter and group repetitive exceptions by carrier in the frontend
 * - avoid having to infer the carrier indirectly from individual exceptions
 *
 * This field belongs on the repetitive exception level because the carrier
 * is part of the recurring problem itself, not just a single occurrence.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Apply the migration.
     *
     * Adds the "carrier" column to the repetitive_exceptions table
     * so each recurring issue can be linked to a specific carrier.
     */
    public function up()
    {
        Schema::table('repetitive_exceptions', function (Blueprint $table) {
            $table->string('carrier');
        });
    }

    /**
     * Reverse the migration.
     *
     * Removes the "carrier" column again if the migration is rolled back.
     */
    public function down()
    {
        Schema::dropColumns('repetitive_exceptions', [
            'carrier',
        ]);
    }
};

