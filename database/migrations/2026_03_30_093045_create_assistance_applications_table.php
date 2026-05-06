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
        Schema::create('assistance_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_code')->nullable();
            $table->unsignedBigInteger('household_id')->nullable();
            $table->unsignedBigInteger('assistance_type_id')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('household_beneficiary_count')->nullable();
            $table->string('status')->default('submitted');
            $table->string('current_stage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistance_applications');
    }
};
