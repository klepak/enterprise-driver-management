<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDellOperatingSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dell_operating_systems', function (Blueprint $table) {
            $table->string("os_code")->primary();
            $table->string("os_vendor");
            $table->string("major_version");
            $table->string("minor_version");
            $table->string("sp_major_version");
            $table->string("sp_minor_version");
            $table->string("os_arch");
            $table->string("description");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dell_operating_systems');
    }
}
