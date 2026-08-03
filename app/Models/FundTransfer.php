<?php

namespace App\Models;

use App\Casts\UserEncryptedMoney;
use App\Enums\TransferStatus;
use Database\Factories\FundTransferFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundTransfer extends Model
{
    /** @use HasFactory<FundTransferFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'savings_plan_id',
        'from_category_id',
        'to_category_id',
        'from_bank_id',
        'to_bank_id',
        'amount_encrypted',
        'description',
        'transferred_on',
        'status',
        'confirmed_at',
        'confirmed_by_user_id',
        'created_by_user_id',
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

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
    }

    public function fromCategory(): BelongsTo
    {
        return $this->belongsTo(SavingsCategory::class, 'from_category_id');
    }

    public function toCategory(): BelongsTo
    {
        return $this->belongsTo(SavingsCategory::class, 'to_category_id');
    }

    public function fromBank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'from_bank_id');
    }

    public function toBank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'to_bank_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function isConfirmed(): bool
    {
        return $this->status === TransferStatus::Confirmed;
    }

    public function crossesBanks(): bool
    {
        return $this->from_bank_id !== $this->to_bank_id;
    }
}
