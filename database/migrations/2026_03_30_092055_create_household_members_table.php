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
        Schema::create('household_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('household_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('relationship')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('education_level')->nullable();
            $table->unsignedBigInteger('occupation_id')->nullable();
            $table->unsignedBigInteger('disability_id')->nullable();
            $table->unsignedBigInteger('chronic_illness_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('household_members');
    }
};
