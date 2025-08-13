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
        Schema::create('education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('institution')->nullable(); // School, University, etc.
            $table->string('degree')->nullable(); // Bachelor's, Master's, PhD, etc.
            $table->string('field_of_study')->nullable();
            $table->string('period')->nullable(); // e.g., "2018-2022" or "2018-Present"
            $table->text('description')->nullable(); // Additional details
            $table->boolean('is_current')->default(false); // For ongoing education
            $table->integer('order')->default(0); // For ordering education entries
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
