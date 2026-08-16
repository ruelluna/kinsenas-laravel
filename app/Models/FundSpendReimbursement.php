<?php

namespace App\Models;

use App\Casts\UserEncryptedMoney;
use Database\Factories\FundSpendReimbursementFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundSpendReimbursement extends Model
{
    /** @use HasFactory<FundSpendReimbursementFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'fund_spend_id',
        'savings_plan_id',
        'category_id',
        'amount_encrypted',
        'received_on',
        'bank_id',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_encrypted' => UserEncryptedMoney::class.':true',
            'received_on' => 'date',
        ];
    }

    public function fundSpend(): BelongsTo
    {
        return $this->belongsTo(FundSpend::class);
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
