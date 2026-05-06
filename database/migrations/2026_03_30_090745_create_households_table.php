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
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('lga_id')->nullable();
            $table->unsignedBigInteger('ward_id')->nullable();
            $table->unsignedBigInteger('community_id')->nullable();
            $table->text('house_address')->nullable();
            $table->string('household_size')->nullable();
            $table->unsignedBigInteger('male_members')->nullable();
            $table->unsignedBigInteger('female_members')->nullable();
            $table->unsignedBigInteger('children_count')->nullable();
            $table->unsignedBigInteger('elderly_count')->nullable();
            $table->decimal('primary_income_source',18,2)->default(0);
            $table->decimal('estimated_monthly_income',18,2)->default(0);
            $table->unsignedBigInteger('housing_condition_id')->nullable();
            $table->string('application_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
