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
            $table->string('role')->default('user');
            $table->foreignId('subscription_tier_id')
                ->nullable()
                ->constrained('subscription_tiers')
                ->nullOnDelete();
            $table->timestamp('subscription_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['subscription_tier_id']);
            $table->dropColumn(['role', 'subscription_tier_id', 'subscription_expires_at']);
        });
    }
};
