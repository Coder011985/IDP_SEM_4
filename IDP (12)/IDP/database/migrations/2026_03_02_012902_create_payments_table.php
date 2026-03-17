<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {

            $table->id();

            // Project
            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            // Milestone (1 payment per milestone)
            $table->foreignId('milestone_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            // Client
            $table->foreignId('client_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Designer
            $table->foreignId('designer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Amount
            $table->decimal('amount', 12, 2);

            // Commission
            $table->decimal('commission_amount', 12, 2)->default(0);

            // Designer payout
            $table->decimal('designer_amount', 12, 2)->default(0);

            // Status
            $table->enum('status', [
                'pending',
                'funded',
                'released'
            ])->default('pending');

            // Tracking
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('released_at')->nullable();

            $table->timestamps();

            // Index optimization
            $table->index('project_id');
            $table->index('milestone_id');
            $table->index('client_id');
            $table->index('designer_id');
            $table->index('status');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
