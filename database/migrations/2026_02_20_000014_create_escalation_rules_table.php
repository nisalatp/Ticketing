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
        Schema::create('escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sla_policy_id')->constrained('sla_policies')->onDelete('cascade');
            $table->enum('trigger', ['no_response', 'no_resolution']);
            $table->integer('after_minutes');
            $table->enum('escalate_to', ['queue_lead', 'dept_head', 'target_id']);
            $table->foreignId('target_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('action', ['notify', 'reassign'])->default('notify');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escalation_rules');
    }
};
