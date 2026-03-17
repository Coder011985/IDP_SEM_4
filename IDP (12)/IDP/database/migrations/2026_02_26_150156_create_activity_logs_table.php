<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('activity_logs', function (Blueprint $table) {

            $table->id();



            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();



            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->cascadeOnDelete();



            $table->string('action');



            $table->text('description')->nullable();



            $table->string('ip_address')->nullable();



            $table->timestamps();



            $table->index('user_id');
            $table->index('project_id');
            $table->index('action');

        });

    }



    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }

};
