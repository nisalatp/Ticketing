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
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
            $table->foreignId('queue_id')->nullable()->change();
        });

        // Seed default ticket types
        $types = [
            ['name' => 'Request', 'icon' => 'InformationCircleIcon', 'color_code' => 'bg-blue-50 text-blue-700 border-blue-100', 'severity_factor' => 1.0],
            ['name' => 'Complaint', 'icon' => 'ExclamationTriangleIcon', 'color_code' => 'bg-orange-50 text-orange-700 border-orange-100', 'severity_factor' => 1.5],
            ['name' => 'Notification', 'icon' => 'DocumentTextIcon', 'color_code' => 'bg-slate-50 text-slate-600 border-slate-100', 'severity_factor' => 1.0],
        ];

        foreach ($types as $type) {
            DB::table('ticket_types')->updateOrInsert(
                ['name' => $type['name']],
                array_merge($type, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
            $table->foreignId('queue_id')->nullable(false)->change();
        });
    }
};
