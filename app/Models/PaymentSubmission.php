<?php

namespace App\Models;

use App\Enums\PaymentSubmissionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSubmission extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'plan_price_id',
        'reference_number',
        'proof_image_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentSubmissionStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function planPrice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlanPrice::class, 'plan_price_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
