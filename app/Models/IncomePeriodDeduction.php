<?php

namespace App\Models;

use App\Casts\UserEncryptedMoney;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomePeriodDeduction extends Model
{
    use HasUuids;

    protected $fillable = [
        'income_period_id',
        'category_id',
        'amount_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'amount_encrypted' => UserEncryptedMoney::class.':true',
        ];
    }

    public function incomePeriod(): BelongsTo
    {
        return $this->belongsTo(IncomePeriod::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SavingsCategory::class, 'category_id');
    }
}
