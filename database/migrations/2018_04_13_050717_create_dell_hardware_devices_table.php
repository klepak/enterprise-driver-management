<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDellHardwareDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dell_hardware_devices', function (Blueprint $table) {
            $table->integer("component_id")->primary();
            $table->string("description");
            $table->integer("embedded");
            $table->text("pci_info")->nullable();
            $table->text("pnp_info")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dell_hardware_devices');
    }
}
