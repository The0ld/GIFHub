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
        Schema::create('service_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('service');
            $table->json('request_body');
            $table->integer('response_status');
            $table->json('response_body');
            $table->string('ip_address');
            $table->string('duration');
            $table->timestamps();

            // Relationships
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // Indexes
            $table->index('user_id', 'idx_service_logs_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_logs');
    }
};
