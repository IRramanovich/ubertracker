<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldToShiftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Shifts', function (Blueprint $table) {
            $table->float('mileage_start', 8, 2)->default(0);
            $table->float('mileage_end', 8, 2)->default(0);
            $table->float('fuel_start', 8, 2)->default(0);
            $table->float('refill', 8, 2)->default(0);
            $table->float('fuel_end', 8, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Shifts', function (Blueprint $table) {
            $table->dropColumn(['mileage_start', 'mileage_end', 'fuel_start', 'refill', 'fuel_end']);
        });
    }
}
