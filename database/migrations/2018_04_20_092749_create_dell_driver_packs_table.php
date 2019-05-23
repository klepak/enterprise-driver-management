<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDellDriverPacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dell_driver_packs', function (Blueprint $table) {
            $table->string("release_id")->primary();
            $table->string("hash_md5");
            $table->string("path");
            $table->string("date_time");
            $table->string("vendor_version");
            $table->string("dell_version");
            $table->string("type");
            $table->integer("size");

            $table->string("name");
            $table->text("description");
            $table->text("supported_operating_systems");
            $table->text("supported_operating_system_languages");
            $table->text("supported_systems");
            $table->string("info_url");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dell_driver_packs');
    }
}
