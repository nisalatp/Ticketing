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
        // First, migrate existing data
        $policies = \Illuminate\Support\Facades\DB::table('sla_policies')->get();
        
        foreach ($policies as $policy) {
            \Illuminate\Support\Facades\DB::table('sla_policy_scopes')->insert([
                'sla_policy_id' => $policy->id,
                'department_id' => $policy->department_id,
                'topic_id' => $policy->topic_id,
                'priority' => $policy->priority,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop the old columns
        Schema::table('sla_policies', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['department_id']);
                $table->dropForeign(['topic_id']);
            }
            $table->dropColumn(['department_id', 'topic_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sla_policies', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->string('priority')->nullable(); // Urgent, High, Medium, Low
        });

        // Migrate back if possible (taking the first scope)
        $scopes = \Illuminate\Support\Facades\DB::table('sla_policy_scopes')->get()->groupBy('sla_policy_id');
        foreach ($scopes as $policyId => $policyScopes) {
            $firstScope = $policyScopes->first();
            \Illuminate\Support\Facades\DB::table('sla_policies')
                ->where('id', $policyId)
                ->update([
                    'department_id' => $firstScope->department_id,
                    'topic_id' => $firstScope->topic_id,
                    'priority' => $firstScope->priority,
                ]);
        }
    }
};
