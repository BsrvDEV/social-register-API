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
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->longText('description');
            $table->string('model_type');
            $table->string('ip_address');
            $table->text('machine_name');
            // $table->string('url')->nullable();
            $table->string('auditable_id')->nullable();
            $table->text('url');
            $table->unsignedBigInteger('model_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_trails', function (Blueprint $table) {
            //
        });
    }
};
