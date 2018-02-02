<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Shifts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('driver_id', 50);
            $table->decimal('total', 4, 2);
            $table->decimal('surge', 4, 2);
            $table->integer('offline_surge');
            $table->integer('offline_not_surge');
            $table->dateTime('start');
            $table->dateTime('end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Shifts');
    }
}
