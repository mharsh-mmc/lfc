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
        Schema::create('family_tree_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->default('Custom');
            $table->string('type')->default('custom'); // custom, vertical, horizontal, circular
            $table->json('layout_data'); // Node positions and edge data
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->unique(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_tree_layouts');
    }
};
