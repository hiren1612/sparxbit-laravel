<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// camelCase user shape (matches what the web + mobile clients expect).
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'emailVerifiedAt' => $this->email_verified_at?->toISOString(),
            'fullName' => $this->name,
            'username' => $this->username,
            'bio' => $this->bio,
            'avatarUrl' => $this->avatar_url,
            'timezone' => $this->timezone,
            'weekStartsOn' => $this->week_starts_on,
            'theme' => $this->theme,
            'language' => $this->language,
            'notificationsPushEnabled' => (bool) $this->notifications_push_enabled,
            'defaultReminderTime' => $this->default_reminder_time,
            'notificationsWeeklySummary' => (bool) $this->notifications_weekly_summary,
            'notificationsProductUpdates' => (bool) $this->notifications_product_updates,
            'status' => $this->status,
            'onboardingCompletedAt' => $this->onboarding_completed_at?->toISOString(),
            'archivedAt' => $this->archived_at?->toISOString(),
            'autoSync' => (bool) $this->auto_sync,
            'referralCode' => $this->referral_code,
            'referredByUserId' => $this->referred_by_user_id,
            'premiumTier' => $this->premium_tier,
            'businessName' => $this->business_name,
            'adTagline' => $this->ad_tagline,
            'adLink' => $this->ad_link,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
