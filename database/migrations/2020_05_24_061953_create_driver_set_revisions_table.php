<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriverSetRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_set_revisions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('status');
            $table->integer('locked');
            $table->integer('revision');
            $table->string('wmi_condition');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('driver_set_revisions');
    }
}
