<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('structured_exception', function (Blueprint $table) {
            $table->string('cfl');
        });
    }

    public function down()
    {
        Schema::dropColumns('structured_exception', [
            'cfl',
        ]);
    }
};

