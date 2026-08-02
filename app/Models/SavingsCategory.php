<?php

namespace App\Models;

use App\Casts\UserEncryptedMoney;
use App\Enums\CategoryAllocationType;
use App\Enums\DeductionMode;
use Database\Factories\SavingsCategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsCategory extends Model
{
    /** @use HasFactory<SavingsCategoryFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'plan_id',
        'name',
        'allocation_type',
        'percentage',
        'deduction_mode',
        'deduction_value',
        'deduct_from_category_id',
        'sort_order',
        'bank_id',
        'opening_balance_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'allocation_type' => CategoryAllocationType::class,
            'percentage' => 'decimal:2',
            'deduction_mode' => DeductionMode::class,
            'deduction_value' => 'decimal:2',
            'opening_balance_encrypted' => UserEncryptedMoney::class.':true',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SavingsPlan::class, 'plan_id');
    }

    public function deductFromCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'deduct_from_category_id');
    }

    public function deductionsFromThis(): HasMany
    {
        return $this->hasMany(self::class, 'deduct_from_category_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(IncomeAllocation::class, 'category_id');
    }

    public function fundSpends(): HasMany
    {
        return $this->hasMany(FundSpend::class, 'category_id');
    }

    public function outgoingFundTransfers(): HasMany
    {
        return $this->hasMany(FundTransfer::class, 'from_category_id');
    }

    public function incomingFundTransfers(): HasMany
    {
        return $this->hasMany(FundTransfer::class, 'to_category_id');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function isPercentage(): bool
    {
        return $this->allocation_type === CategoryAllocationType::Percentage;
    }

    public function isDeduction(): bool
    {
        return $this->allocation_type === CategoryAllocationType::Deduction;
    }
}
