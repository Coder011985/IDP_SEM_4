<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('milestone_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('opened_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('reason');

            $table->enum('status',[
                'open',
                'under_review',
                'resolved',
                'rejected'
            ])->default('open');

            $table->enum('resolution',[
                'refund_client',
                'pay_designer',
                'partial'
            ])->nullable();

            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
