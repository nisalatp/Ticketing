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
        Schema::table('users', function (Blueprint $table) {
            $table->string('auth_type')->default('local')->after('remember_token');
            $table->boolean('must_reset_password')->default(false)->after('auth_type');
        });

        // Data migration: existing users with ms_oid are 'microsoft' accounts
        DB::table('users')->whereNotNull('ms_oid')->update(['auth_type' => 'microsoft']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['auth_type', 'must_reset_password']);
        });
    }
};
