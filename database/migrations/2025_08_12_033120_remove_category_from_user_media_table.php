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
        Schema::table('user_media', function (Blueprint $table) {
            // Remove the category index first
            $table->dropIndex(['category']);
            
            // Remove the category column
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_media', function (Blueprint $table) {
            // Add the category column back
            $table->string('category')->nullable();
            
            // Add the category index back
            $table->index('category');
        });
    }
};
