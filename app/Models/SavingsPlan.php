<?php

namespace App\Models;

use Database\Factories\SavingsPlanFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsPlan extends Model
{
    /** @use HasFactory<SavingsPlanFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id',
        'created_by_user_id',
        'name',
        'currency',
        'is_shared_with_team',
        'allow_editing_spends',
    ];

    protected function casts(): array
    {
        return [
            'is_shared_with_team' => 'boolean',
            'allow_editing_spends' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(SavingsCategory::class, 'plan_id')->orderBy('sort_order');
    }

    public function incomePeriods(): HasMany
    {
        return $this->hasMany(IncomePeriod::class, 'plan_id')->latest('period_start');
    }

    public function fundSpends(): HasMany
    {
        return $this->hasMany(FundSpend::class, 'savings_plan_id');
    }

    public function hasLockedIncomePeriod(): bool
    {
        return $this->incomePeriods()->where('is_locked', true)->exists();
    }

    public function hasIncomePeriod(): bool
    {
        return $this->incomePeriods()->exists();
    }

    public function hasOpeningBalances(): bool
    {
        return $this->categories()
            ->whereNotNull('opening_balance_encrypted')
            ->exists();
    }

    public function shouldShowFundBalances(): bool
    {
        return $this->categories()->exists();
    }

    public function canDrawFromFunds(): bool
    {
        return $this->hasOpeningBalances() || $this->hasLockedIncomePeriod();
    }
}
