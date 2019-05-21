<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHpSoftpaqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hp_softpaqs', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
            $table->string('version');
            $table->string('category');
            $table->string('date_released');
            $table->string('purpose');
            $table->string('url');
            $table->string('size');
            $table->text('supported_languages');
            $table->text('supported_os');
            $table->string('cva_file_url');
            $table->string('release_notes_url');
            $table->string('silent_install');
            $table->integer('ssm_compliant');
            $table->integer('dpb_compliant');
            $table->string('md5');
            $table->string('vendor_name');
            $table->string('vendor_version');
            $table->string('col_id');
            $table->string('item_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hp_softpaqs');
    }
}
