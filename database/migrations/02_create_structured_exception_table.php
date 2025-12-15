<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

    public function down()
    {
        Schema::dropIfExists('structured_exception');
    }
};

