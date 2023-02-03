<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApplicantRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('applicant_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('nal_id');
            $table->unsignedInteger('lga_id');
            $table->unsignedInteger('my_class_id');
            $table->unsignedInteger('section_id');
            $table->string('application_no', 30)->unique()->nullable();
            $table->unsignedInteger('my_parent_id')->nullable();
            $table->string('last_class')->nullable();
            $table->string('class_qualified')->nullable();
            $table->string('session');
            $table->string('fullname')->nullable();
            $table->string('parent_address')->nullable();
            $table->string('parent_occupation')->nullable();
            $table->string('home_address')->nullable();
            $table->string('name_of_peson_who_picks_ward')->nullable();
            $table->string('email')->nullable();
            $table->string('religion')->nullable();
            $table->string('certificate_obtained')->nullable();
            $table->string('been_to_sch', 10);
            $table->string('certification', 10);
            $table->string('application_status', 10);
            $table->string('dob');
            $table->string('year_admitted')->nullable();
            $table->string('date_of_entrance_exam')->nullable();
            $table->string('passport')->nullable();

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
        Schema::dropIfExists('applicant_records');
    }
}
