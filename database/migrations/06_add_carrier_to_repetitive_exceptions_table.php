<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('repetitive_exceptions', function (Blueprint $table) {
            $table->string('carrier');
        });
    }

    public function down()
    {
        Schema::dropColumns('repetitive_exceptions', [
            'carrier',
        ]);


    }
};

