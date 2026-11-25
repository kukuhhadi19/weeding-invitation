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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_tier_id')->constrained()->restrictOnDelete();
            $table->string('midtrans_order_id')->unique();
            $table->string('midtrans_transaction_id')->nullable()->index();
            $table->unsignedInteger('amount');
            $table->string('status')->default('pending')->index();
            $table->string('payment_type')->nullable();
            $table->string('snap_token')->nullable();
            $table->json('midtrans_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
