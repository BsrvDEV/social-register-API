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
        Schema::create('field_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assistance_application_id')->nullable();
            $table->unsignedBigInteger('officer_id')->nullable();
            $table->date('visit_date')->nullable();
            $table->unsignedBigInteger('status')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_reports');
    }
};
