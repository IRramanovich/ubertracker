<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Trips', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('driver_id', 50);
            $table->decimal('total', 4, 2);
            $table->integer('duration');
            $table->decimal('distance', 4, 2);
            $table->dateTime('begin_trip_at');
            $table->dateTime('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('Trips');
    }
}
