<?php

namespace Database\Seeders;

use App\Models\SubscriptionTier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enterpriseTier = SubscriptionTier::where('slug', 'enterprise')->first();

        User::updateOrCreate(
            ['email' => 'admin@wedding.test'],
            [
                'name' => 'Admin Platform',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'subscription_tier_id' => $enterpriseTier?->id,
            ]
        );
    }
}
