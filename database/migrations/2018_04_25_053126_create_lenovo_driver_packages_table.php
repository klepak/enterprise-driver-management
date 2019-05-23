<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLenovoDriverPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lenovo_driver_packages', function (Blueprint $table) {
            $table->string('package_id')->primary();
            $table->string('type')->nullable();
            $table->string('category')->nullable();
            $table->string('name')->nullable();
            $table->string('download_url')->nullable();
            $table->string('install_cmd')->nullable();
            $table->string('date')->nullable();
            $table->text('supported_models');
            $table->text('supported_operating_systems');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lenovo_driver_packages');
    }
}
