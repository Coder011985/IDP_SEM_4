<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('bids', function (Blueprint $table) {

            $table->id();



            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();



            $table->foreignId('designer_id')
                ->constrained('users')
                ->cascadeOnDelete();



            $table->decimal('amount',12,2);



            $table->text('proposal');



            $table->enum('status',[
                'pending',
                'accepted',
                'rejected',
                'withdrawn'
            ])->default('pending');



            $table->timestamps();



            $table->unique(['project_id','designer_id']);



            $table->index('project_id');
            $table->index('designer_id');
            $table->index('status');

        });

    }



    public function down(): void
    {
        Schema::dropIfExists('bids');
    }

};
