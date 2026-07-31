<?php

namespace App\Models;

use App\Casts\UserEncryptedMoney;
use App\Enums\TransferStatus;
use Database\Factories\FundSpendFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundSpend extends Model
{
    /** @use HasFactory<FundSpendFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'savings_plan_id',
        'category_id',
        'amount_encrypted',
        'description',
        'spent_on',
        'bank_id',
        'recipient_id',
        'status',
        'confirmed_at',
        'confirmed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_encrypted' => UserEncryptedMoney::class.':true',
            'status' => TransferStatus::class,
            'spent_on' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
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

    public function isConfirmed(): bool
    {
        return $this->status === TransferStatus::Confirmed;
    }
}
