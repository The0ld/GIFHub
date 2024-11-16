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
        Schema::create('favorite_gifs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('gif_id');
            $table->string('alias', 20);
            $table->timestamps();

            // Uniques
            $table->unique(['user_id', 'gif_id']);

            // foreign keys
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // Indexes
            $table->index('user_id', 'idx_favorite_gifs_user_id');
            $table->index('gif_id', 'idx_favorite_gifs_gif_id');
            $table->index(['user_id', 'gif_id'], 'idx_favorite_gifs_user_id_gif_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_gifs');
    }
};
