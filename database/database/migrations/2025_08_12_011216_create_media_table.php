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
        Schema::create('user_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('media_type', ['video', 'image', 'letter', 'document']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(false);
            $table->json('metadata')->nullable(); // For additional data like video duration, etc.
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'media_type']);
            $table->index(['media_type', 'is_private']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_media');
    }
};
