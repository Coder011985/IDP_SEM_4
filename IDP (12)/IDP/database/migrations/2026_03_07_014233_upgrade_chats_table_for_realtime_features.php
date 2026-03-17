<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {

            if (!Schema::hasColumn('chats', 'file_path')) {
                $table->string('file_path')->nullable()->after('message');
            }

            if (!Schema::hasColumn('chats', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('is_read');
            }

            if (!Schema::hasColumn('chats', 'seen_at')) {
                $table->timestamp('seen_at')->nullable()->after('delivered_at');
            }

        });
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {

            if (Schema::hasColumn('chats', 'seen_at')) {
                $table->dropColumn('seen_at');
            }

        });
    }
};
