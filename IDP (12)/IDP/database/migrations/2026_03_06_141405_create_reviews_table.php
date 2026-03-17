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
        Schema::create('reviews', function (Blueprint $table) {

            $table->id();

            // Client who gives review
            $table->foreignId('reviewer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Designer who receives review
            $table->foreignId('designer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Related project
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            // Rating (1–5 stars)
            $table->unsignedTinyInteger('rating');

            // Written feedback
            $table->text('review')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
