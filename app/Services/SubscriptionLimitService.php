<?php

namespace App\Services;

use App\Models\SubscriptionTier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubscriptionLimitService
{
    /**
     * Resolve the effective subscription tier for a user.
     * Falls back to the Free tier if unassigned or if a paid subscription has expired.
     */
    public function resolveTier(User $user): SubscriptionTier
    {
        $tier = $user->subscriptionTier;

        if ($tier && $tier->price_monthly > 0 && $user->subscription_expires_at?->isPast()) {
            $tier = null;
        }

        if ($tier) {
            return $tier;
        }

        return SubscriptionTier::where('slug', 'free')->first()
            ?? new SubscriptionTier([
                'name' => 'Free',
                'slug' => 'free',
                'price_monthly' => 0,
                'max_active_invitations' => 1,
                'max_gallery_photos' => 5,
                'remove_watermark' => false,
                'custom_slug' => false,
                'priority_support' => false,
                'analytics_level' => 'none',
            ]);
    }

    /**
     * Count active (non-archived) invitations belonging to the user.
     * Safely returns 0 before the invitations table exists in Issue #8.
     */
    public function countActiveInvitations(User $user): int
    {
        if (! Schema::hasTable('invitations')) {
            return 0;
        }

        return (int) DB::table('invitations')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'archived')
            ->count();
    }

    /**
     * Determine whether the user can create another active invitation.
     */
    public function canCreateInvitation(User $user, ?int $activeCount = null): bool
    {
        $tier = $this->resolveTier($user);

        if ($tier->max_active_invitations === -1) {
            return true;
        }

        $used = $activeCount ?? $this->countActiveInvitations($user);

        return $used < $tier->max_active_invitations;
    }

    /**
     * Determine whether the user has reached or exceeded their active invitation quota.
     */
    public function hasReachedInvitationLimit(User $user, ?int $activeCount = null): bool
    {
        return ! $this->canCreateInvitation($user, $activeCount);
    }

    /**
     * Return remaining active invitation slots (-1 denotes unlimited).
     */
    public function remainingInvitations(User $user, ?int $activeCount = null): int
    {
        $tier = $this->resolveTier($user);

        if ($tier->max_active_invitations === -1) {
            return -1;
        }

        $used = $activeCount ?? $this->countActiveInvitations($user);

        return max(0, $tier->max_active_invitations - $used);
    }

    /**
     * Determine whether the user can upload another photo to an invitation gallery.
     */
    public function canUploadGalleryPhoto(User $user, int $currentPhotoCount): bool
    {
        $tier = $this->resolveTier($user);

        if ($tier->max_gallery_photos === -1) {
            return true;
        }

        return $currentPhotoCount < $tier->max_gallery_photos;
    }

    /**
     * Determine whether the user's tier permits custom invitation slugs.
     */
    public function canUseCustomSlug(User $user): bool
    {
        return (bool) $this->resolveTier($user)->custom_slug;
    }

    /**
     * Determine whether the user's tier grants access to a template.
     */
    public function canUseTemplate(User $user, bool $isPremiumTemplate): bool
    {
        if (! $isPremiumTemplate) {
            return true;
        }

        return $this->resolveTier($user)->slug !== 'free';
    }

    /**
     * Build a structured quota summary for UI views (Dashboard, Account, Subscription).
     *
     * @return array{
     *     tier: SubscriptionTier,
     *     used: int,
     *     limit: int,
     *     remaining: int,
     *     is_unlimited: bool,
     *     usage_percentage: int,
     *     limit_reached: bool,
     *     max_gallery_photos: int,
     *     remove_watermark: bool,
     *     custom_slug: bool,
     *     priority_support: bool,
     *     analytics_level: string,
     * }
     */
    public function getQuotaSummary(User $user, ?int $activeCount = null): array
    {
        $tier = $this->resolveTier($user);
        $used = $activeCount ?? $this->countActiveInvitations($user);
        $limit = (int) $tier->max_active_invitations;
        $isUnlimited = $limit === -1;

        $usagePercentage = $isUnlimited
            ? 0
            : ($limit > 0 ? min(100, (int) round(($used / $limit) * 100)) : 100);

        return [
            'tier' => $tier,
            'used' => $used,
            'limit' => $limit,
            'remaining' => $isUnlimited ? -1 : max(0, $limit - $used),
            'is_unlimited' => $isUnlimited,
            'usage_percentage' => $usagePercentage,
            'limit_reached' => $isUnlimited ? false : $used >= $limit,
            'max_gallery_photos' => (int) $tier->max_gallery_photos,
            'remove_watermark' => (bool) $tier->remove_watermark,
            'custom_slug' => (bool) $tier->custom_slug,
            'priority_support' => (bool) $tier->priority_support,
            'analytics_level' => (string) $tier->analytics_level,
        ];
    }
}
