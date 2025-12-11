<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

    public function down()
    {
        Schema::dropIfExists('exceptions');
    }
};
