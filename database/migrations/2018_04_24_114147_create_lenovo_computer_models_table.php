<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLenovoComputerModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lenovo_computer_models', function (Blueprint $table) {
            $table->string("id")->primary();
            $table->string("system_id");
            $table->string("product_family");
            #$table->string("operating_system");
            $table->text("types");
            $table->string("name");
            $table->string("smbios");
            #$table->text("driver_packs");
            #$table->string("bios_update");
            #$table->text("hardware_apps");
            $table->text("supported_operating_systems");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lenovo_computer_models');
    }
}
