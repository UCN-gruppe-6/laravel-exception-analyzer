<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('structured_exception', function (Blueprint $table) {
            $table->string('file_name');
            $table->string('line_number');
            $table->string('code');
        });

        Schema::table('exceptions', function (Blueprint $table) {
            $table->text('message')->change();
        });
    }

    public function down()
    {
        Schema::dropColumns('structured_exception', [
            'file_name',
            'line_number',
            'code',
        ]);

        Schema::table('exceptions', function (Blueprint $table) {
            $table->string('message')->change();
        });
    }
};

