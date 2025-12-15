<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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

        Schema::table('structured_exception', function (Blueprint $table) {
            $table->integer('repetitive_exception_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('repetitive_exceptions');

        Schema::dropColumns('structured_exception', [
            'repetitive_exception_id',
        ]);


    }
};

