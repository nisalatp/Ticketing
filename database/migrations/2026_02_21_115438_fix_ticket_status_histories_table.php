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
        if (Schema::hasTable('ticket_status_history')) {
            Schema::rename('ticket_status_history', 'ticket_status_histories');
        }

        $table = 'ticket_status_histories';

        if (!Schema::hasColumn($table, 'user_id')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            });
        }
        if (!Schema::hasColumn($table, 'event_type')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('event_type')->nullable();
            });
        }
        if (!Schema::hasColumn($table, 'description')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->text('description')->nullable();
            });
        }
        if (!Schema::hasColumn($table, 'from_state')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('from_state')->nullable();
            });
        }
        if (!Schema::hasColumn($table, 'to_state')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('to_state')->nullable();
            });
        }
        if (!Schema::hasColumn($table, 'updated_at')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->timestamp('updated_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need for complex down for this recovery migration
    }
};
