<?php

namespace App\Models;

use App\Casts\UserEncryptedMoney;
use Database\Factories\IncomePeriodFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomePeriod extends Model
{
    /** @use HasFactory<IncomePeriodFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'plan_id',
        'name',
        'amount_encrypted',
        'period_start',
        'is_locked',
        'locked_at',
        'locked_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_encrypted' => UserEncryptedMoney::class.':true',
            'period_start' => 'date',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SavingsPlan::class, 'plan_id');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by_user_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(IncomeAllocation::class);
    }

    public function periodDeductions(): HasMany
    {
        return $this->hasMany(IncomePeriodDeduction::class);
    }

    public function distributionTodos(): HasMany
    {
        return $this->hasMany(IncomeDistributionTodo::class);
    }
}
