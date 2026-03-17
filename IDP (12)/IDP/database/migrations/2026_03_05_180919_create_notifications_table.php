<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {

            $table->id();

            // USER INDEX (important for fast queries)
            $table->foreignId('user_id')
                  ->index()
                  ->constrained()
                  ->cascadeOnDelete();

            // TYPE OF NOTIFICATION
            $table->string('type');

            // MESSAGE BODY
            $table->text('message');

            // OPTIONAL LINK
            $table->string('link')->nullable();

            // READ STATUS INDEX (improves unread queries)
            $table->boolean('is_read')
                  ->default(false)
                  ->index();

            // CREATED_AT INDEX (improves sorting)
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
