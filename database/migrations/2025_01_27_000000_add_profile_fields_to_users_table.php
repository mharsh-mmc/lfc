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
        Schema::table('users', function (Blueprint $table) {
            // Basic profile information
            $table->string('title')->nullable(); // Teacher, Inventor, CEO
            $table->date('date_of_birth')->nullable();
            $table->string('location')->nullable(); // Italy, Foggia
            $table->text('bio')->nullable();
            
            // Physical attributes
            $table->integer('height_cm')->nullable();
            $table->integer('weight_kg')->nullable();
            
            // Education
            $table->string('university')->nullable();
            $table->string('field_of_study')->nullable();
            $table->string('education_period')->nullable(); // 1852 - 1854
            
            // Personal questions
            $table->text('passion')->nullable(); // What do you love to do?
            $table->text('profession')->nullable(); // What are you good at?
            $table->text('mission')->nullable(); // What does the world need?
            $table->text('calling')->nullable(); // Natural inclination
            
            // Social features
            $table->integer('connections_count')->default(0);
            $table->integer('tributes_count')->default(0);
            $table->integer('flowers_count')->default(0);
            
            // Profile settings
            $table->boolean('is_public')->default(false);
            $table->timestamp('last_activity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'date_of_birth',
                'location',
                'bio',
                'height_cm',
                'weight_kg',
                'university',
                'field_of_study',
                'education_period',
                'passion',
                'profession',
                'mission',
                'calling',
                'connections_count',
                'tributes_count',
                'flowers_count',
                'is_public',
                'last_activity'
            ]);
        });
    }
}; 