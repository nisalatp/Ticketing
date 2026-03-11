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
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->string('icon')->default('TicketIcon')->after('name');
            $table->string('color_code')->default('bg-slate-50 text-slate-700')->after('icon');
            $table->decimal('severity_factor', 4, 2)->default(1.00)->after('color_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn(['icon', 'color_code', 'severity_factor']);
        });
    }
};
