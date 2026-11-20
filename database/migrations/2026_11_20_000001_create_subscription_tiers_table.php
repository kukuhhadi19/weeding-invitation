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
        Schema::create('subscription_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('price_monthly')->default(0);
            $table->integer('max_active_invitations')->default(1);
            $table->integer('max_gallery_photos')->default(5);
            $table->boolean('remove_watermark')->default(false);
            $table->boolean('custom_slug')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->string('analytics_level')->default('none');
            $table->json('features_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_tiers');
    }
};
