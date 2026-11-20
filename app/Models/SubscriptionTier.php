<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price_monthly',
        'max_active_invitations',
        'max_gallery_photos',
        'remove_watermark',
        'custom_slug',
        'priority_support',
        'analytics_level',
        'features_json',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'integer',
            'max_active_invitations' => 'integer',
            'max_gallery_photos' => 'integer',
            'remove_watermark' => 'boolean',
            'custom_slug' => 'boolean',
            'priority_support' => 'boolean',
            'features_json' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
