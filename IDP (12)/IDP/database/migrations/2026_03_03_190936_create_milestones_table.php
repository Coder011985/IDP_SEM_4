<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('milestones', function (Blueprint $table) {

            $table->id();


            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();



            $table->string('title');

            $table->text('description')->nullable();



            $table->decimal('amount',12,2);

            $table->unsignedTinyInteger('percentage')->nullable();



            $table->unsignedInteger('position')->default(1);



            $table->enum('status',[
                'pending',
                'approved',
                'funded',
                'released'
            ])->default('pending');



            $table->date('due_date')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamp('funded_at')->nullable();

            $table->timestamp('released_at')->nullable();



            $table->timestamps();



            $table->index('project_id');
            $table->index('status');
            $table->index('position');

        });

    }



    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }

};
