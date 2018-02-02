<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Shifts', function ($table) {
            $table->decimal('total', 6, 2)->default(0.00)->change();
            $table->decimal('surge', 6, 2)->default(0.00)->change();
            $table->integer('offline_surge')->default(0)->change();
            $table->integer('offline_not_surge')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Shifts', function ($table) {
            $table->decimal('total', 4, 2)->change();
        });
    }
}
