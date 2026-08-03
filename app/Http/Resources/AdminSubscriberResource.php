<?php

namespace App\Http\Resources;

use App\Models\Team;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Team */
class AdminSubscriberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $subscription = $this->subscription;
        $owner = $this->owner();
        $subscriptionService = app(SubscriptionService::class);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'isPersonal' => $this->is_personal,
            'ownerName' => $owner?->name,
            'ownerEmail' => $owner?->email,
            'subscription' => $subscription ? [
                'status' => $subscription->status->value,
                'statusLabel' => $subscription->status->label(),
                'planName' => $subscription->plan?->name,
                'hasAccess' => $subscriptionService->teamHasAccess($this->resource),
            ] : null,
            'createdAt' => $this->created_at->toISOString(),
        ];
    }
}
