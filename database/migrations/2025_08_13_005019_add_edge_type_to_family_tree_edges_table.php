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
        Schema::table('family_tree_edges', function (Blueprint $table) {
            $table->string('edge_type')->default('bezier')->after('relationship_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_tree_edges', function (Blueprint $table) {
            $table->dropColumn('edge_type');
        });
    }
};
