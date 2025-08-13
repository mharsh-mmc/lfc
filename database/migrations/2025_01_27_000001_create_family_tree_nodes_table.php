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
        Schema::create('family_tree_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('profile_id')->constrained('users')->onDelete('cascade');
            $table->string('relation')->default('unknown'); // parent, child, spouse, sibling, etc.
            $table->integer('x_position')->default(0);
            $table->integer('y_position')->default(0);
            $table->json('custom_data')->nullable(); // Additional profile data
            $table->timestamps();
            
            $table->unique(['user_id', 'profile_id']);
            $table->index(['user_id', 'relation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_tree_nodes');
    }
};
