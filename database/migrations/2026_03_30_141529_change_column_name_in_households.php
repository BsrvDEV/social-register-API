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
        Schema::table('households', function (Blueprint $table) {
            $table->renameColumn('ward_id', 'ward')->change();
            $table->renameColumn('community_id', 'community')->change();
            $table->string('primary_income_source')->change();
            $table->renameColumn('housing_condition_id', 'housing_condition')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            //
        });
    }
};
