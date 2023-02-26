<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCertificateObtainedAndScheduleToApplicantRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applicant_records', function (Blueprint $table) {
            //
            // $table->string('certificate_obtained', 100);
            $table->string('examination_hall', 150);
            $table->string('applicant_schedule', 20);
            $table->string('applicant_type', 20);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_records', function (Blueprint $table) {
            //
        });
    }
}
