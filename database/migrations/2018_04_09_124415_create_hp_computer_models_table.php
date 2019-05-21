<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHpComputerModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hp_computer_models', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('short_name');
            $table->string('system_id');
            $table->string('dpb_compliant');
            $table->string('supported_os_ids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hp_computer_models');
    }
}
