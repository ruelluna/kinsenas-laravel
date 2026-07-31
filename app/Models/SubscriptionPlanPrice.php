<?php

namespace App\Models;

use App\Enums\BillingInterval;
use Database\Factories\SubscriptionPlanPriceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlanPrice extends Model
{
    /** @use HasFactory<SubscriptionPlanPriceFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'plan_id',
        'interval',
        'amount',
        'currency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'interval' => BillingInterval::class,
            'is_active' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function paymentSubmissions(): HasMany
    {
        return $this->hasMany(PaymentSubmission::class, 'plan_price_id');
    }
}
