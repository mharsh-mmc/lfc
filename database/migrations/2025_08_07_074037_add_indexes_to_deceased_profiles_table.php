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
        Schema::table('deceased_profiles', function (Blueprint $table) {
            // Add indexes for better query performance
            $table->index(['is_public', 'death_date'], 'idx_public_death_date');
            $table->index(['created_by', 'created_at'], 'idx_creator_created');
            $table->index(['name'], 'idx_name');
            $table->index(['birth_date', 'death_date'], 'idx_dates');
            
            // Full text search indexes for better search performance
            $table->fullText(['name', 'memorial_message', 'biography'], 'idx_fulltext_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deceased_profiles', function (Blueprint $table) {
            $table->dropIndex('idx_public_death_date');
            $table->dropIndex('idx_creator_created');
            $table->dropIndex('idx_name');
            $table->dropIndex('idx_dates');
            $table->dropFullText('idx_fulltext_search');
        });
    }
};
