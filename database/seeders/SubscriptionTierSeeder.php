<?php

namespace Database\Seeders;

use App\Models\SubscriptionTier;
use Illuminate\Database\Seeder;

class SubscriptionTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price_monthly' => 0,
                'max_active_invitations' => 1,
                'max_gallery_photos' => 5,
                'remove_watermark' => false,
                'custom_slug' => false,
                'priority_support' => false,
                'analytics_level' => 'none',
                'features_json' => json_encode(['watermark' => true, 'analytics' => 'none']),
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price_monthly' => 49000,
                'max_active_invitations' => 3,
                'max_gallery_photos' => 15,
                'remove_watermark' => true,
                'custom_slug' => false,
                'priority_support' => false,
                'analytics_level' => 'basic',
                'features_json' => json_encode(['watermark' => false, 'analytics' => 'basic']),
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'price_monthly' => 99000,
                'max_active_invitations' => 10,
                'max_gallery_photos' => 30,
                'remove_watermark' => true,
                'custom_slug' => true,
                'priority_support' => false,
                'analytics_level' => 'full',
                'features_json' => json_encode(['watermark' => false, 'custom_slug' => true, 'analytics' => 'full']),
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price_monthly' => 249000,
                'max_active_invitations' => -1, // Unlimited
                'max_gallery_photos' => -1,      // Unlimited
                'remove_watermark' => true,
                'custom_slug' => true,
                'priority_support' => true,
                'analytics_level' => 'full_export',
                'features_json' => json_encode(['watermark' => false, 'custom_slug' => true, 'priority_support' => true, 'analytics' => 'full_export']),
            ],
        ];

        foreach ($tiers as $tier) {
            SubscriptionTier::updateOrCreate(['slug' => $tier['slug']], $tier);
        }
    }
}
