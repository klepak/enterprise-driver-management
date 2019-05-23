<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDellSoftwareComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dell_software_components', function (Blueprint $table) {
            $table->string("identifier");
            $table->string("package_id");
            $table->string("release_id");
            $table->string("hash_md5");
            $table->string("path");
            $table->string("date_time");
            $table->string("release_date");
            $table->string("vendor_version");
            $table->string("dell_version");
            $table->string("package_type");
            $table->integer("reboot_required");
            $table->integer("size");

            $table->string("name");
            $table->string("component_type");
            $table->text("description");
            $table->string("category");
            $table->text("supported_devices");
            $table->text("supported_operating_systems");
            $table->text("supported_operating_system_languages");
            $table->text("supported_systems");
            $table->string("info_url");
            $table->integer("criticality");
            $table->text("criticality_display");
            $table->string("msi_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dell_software_components');
    }
}
