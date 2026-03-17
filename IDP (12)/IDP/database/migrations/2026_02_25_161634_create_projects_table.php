<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('projects', function (Blueprint $table) {

            $table->id();

            $table->foreignId('client_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('designer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            $table->string('title');

            $table->text('description');


            $table->decimal('budget_min',12,2);

            $table->decimal('budget_max',12,2);


            $table->string('type')->nullable();

            $table->string('location')->nullable();

            $table->integer('area_sqft')->nullable();

            $table->date('deadline')->nullable();

            $table->string('style')->nullable();

            $table->string('property_type')->nullable();

            $table->text('requirements')->nullable();



            $table->enum('status',[
                'pending',
                'assigned',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');


            $table->enum('approval_status',[
                'pending',
                'approved',
                'rejected'
            ])->default('pending');


            $table->unsignedTinyInteger('progress')->default(0);


            $table->unsignedTinyInteger('rating')->nullable();

            $table->text('review')->nullable();


            $table->timestamps();



            $table->index('client_id');
            $table->index('designer_id');
            $table->index('status');
            $table->index('approval_status');

        });

    }



    public function down(): void
    {
        Schema::dropIfExists('projects');
    }

};
