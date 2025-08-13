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
        Schema::create('deceased_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('profile_photo_path')->nullable();
            $table->date('birth_date');
            $table->date('death_date');
            $table->string('birth_place')->nullable();
            $table->string('death_place')->nullable();
            $table->text('biography')->nullable();
            $table->text('memorial_message')->nullable();
            $table->string('relationship')->nullable(); // e.g., "Father", "Mother", "Grandfather"
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['created_by', 'is_public']);
            $table->index('death_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deceased_profiles');
    }
};
