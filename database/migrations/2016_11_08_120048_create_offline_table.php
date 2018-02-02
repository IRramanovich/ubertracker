<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfflineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Offline', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shift_id');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->dateTime('is_surge');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('Offline');
    }
}
