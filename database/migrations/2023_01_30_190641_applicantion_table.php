<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApplicantionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('applicantions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('session')->nullable();
            $table->string('name');
            $table->integer('amount');
            $table->string('status', 10);
            $table->string('openning_date')->nullable();
            $table->string('closing_date')->nullable();

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
        //
        Schema::dropIfExists('applicantions');
    }
}
