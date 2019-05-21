<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHpOperatingSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hp_operating_systems', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
            $table->string('ms_name');
            $table->string('ssm_name');
            $table->string('os_base');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hp_operating_systems');
    }
}
