<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApplicantPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('applicant_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('session');
            $table->unsignedInteger('applicant_id');
            $table->string('name');
            $table->string('ref_num');
            $table->integer('application_amount');
            $table->string('status', 10);

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
        Schema::dropIfExists('applicant_payments');
    }
}
