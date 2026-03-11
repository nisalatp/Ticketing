<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE queues MODIFY COLUMN assignment_strategy ENUM('manual', 'round_robin', 'workload', 'least_tickets') NOT NULL DEFAULT 'manual'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE queues MODIFY COLUMN assignment_strategy ENUM('round_robin', 'least_tickets') NOT NULL DEFAULT 'round_robin'");
    }
};
