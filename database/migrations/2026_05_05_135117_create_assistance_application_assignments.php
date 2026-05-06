<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assistance_application_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assistance_application_id')->nullable();
            $table->unsignedBigInteger('applicant_id')->nullable();
            $table->string('officer_name')->nullable();
            $table->unsignedBigInteger('officer_title_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistance_application_assignments');
    }
};
