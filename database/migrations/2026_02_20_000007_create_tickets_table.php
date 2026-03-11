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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique(); // SS-YYYY-######
            $table->foreignId('requester_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('ticket_categories')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('ticket_types')->onDelete('cascade');
            $table->foreignId('queue_id')->constrained()->onDelete('cascade');
            $table->string('priority')->index(); // Low, Medium, High, Urgent
            $table->string('status')->index(); // Draft, Submitted, Assigned, In Progress, Waiting, Resolved, Closed, Reopened
            $table->string('subject');
            $table->text('description');
            $table->boolean('is_confidential_snapshot')->default(false);
            $table->boolean('is_anonymous_snapshot')->default(false);
            $table->foreignId('sla_policy_id_snapshot')->nullable()->constrained('sla_policies')->onDelete('set null');
            $table->foreignId('current_assignee_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_escalated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
