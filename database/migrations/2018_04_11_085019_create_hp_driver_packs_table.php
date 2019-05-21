<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHpDriverPacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hp_driver_packs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('product_type');
            $table->string('system_id');
            $table->string('system_name');
            $table->string('os_name');
            $table->string('softpaq_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hp_driver_packs');
    }
}
