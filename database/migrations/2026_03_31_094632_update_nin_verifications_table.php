<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNinVerificationsTable extends Migration
{
    public function up()
    {
        Schema::table('nin_verifications', function (Blueprint $table) {

            // Identity
            $table->string('title')->nullable();
            $table->string('nationality')->nullable();
            $table->string('email')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->string('state_of_residence')->nullable();
            $table->string('city')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('profession')->nullable();
            $table->string('kin_first_name')->nullable();
            $table->string('kin_last_name')->nullable();
            $table->string('kin_phone')->nullable();
            $table->string('kin_email')->nullable();
            $table->text('kin_address')->nullable();
            $table->string('business_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('rc_number')->nullable();
            $table->text('company_address')->nullable();
            $table->text('photo')->nullable();

        });
    }

    public function down(): void
    {
        //
    }
}
