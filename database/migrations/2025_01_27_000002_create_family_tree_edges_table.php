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
        Schema::create('family_tree_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_node_id')->constrained('family_tree_nodes')->onDelete('cascade');
            $table->foreignId('to_node_id')->constrained('family_tree_nodes')->onDelete('cascade');
            $table->string('relationship_type')->default('family'); // family, marriage, adoption, etc.
            $table->json('edge_data')->nullable(); // Additional edge properties
            $table->timestamps();
            
            $table->unique(['user_id', 'from_node_id', 'to_node_id']);
            $table->index(['user_id', 'relationship_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_tree_edges');
    }
};
