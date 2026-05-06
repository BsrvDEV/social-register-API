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
        Schema::table('household_members', function (Blueprint $table) {
           $table->string('occupation_id')->change();
           $table->string('chronic_illness_id')->change();
        });

        Schema::table('household_members', function (Blueprint $table) {
           $table->renameColumn('occupation_id', 'occupation')->nullable();
           $table->renameColumn('chronic_illness_id', 'chronic_illness')->nullable();
           $table->renameColumn('disability_id', 'disability')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('household_members', function (Blueprint $table) {
            //
        });
    }
};
