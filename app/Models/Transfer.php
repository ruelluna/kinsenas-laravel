<?php

namespace App\Models;

use App\Casts\UserEncryptedMoney;
use App\Enums\TransferStatus;
use Database\Factories\TransferFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    /** @use HasFactory<TransferFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'income_period_id',
        'category_id',
        'bank_id',
        'recipient_id',
        'amount_encrypted',
        'status',
        'transferred_on',
        'confirmed_at',
        'confirmed_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_encrypted' => UserEncryptedMoney::class.':true',
            'status' => TransferStatus::class,
            'transferred_on' => 'date',
            'confirmed_at' => 'datetime',
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

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }
}
