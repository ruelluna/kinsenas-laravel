<?php

namespace App\Models;

use App\Casts\UserEncryptedMoney;
use App\Enums\IncomeDistributionTodoStatus;
use Database\Factories\IncomeDistributionTodoFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeDistributionTodo extends Model
{
    /** @use HasFactory<IncomeDistributionTodoFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'income_period_id',
        'category_id',
        'bank_id',
        'amount_encrypted',
        'status',
        'completed_at',
        'completed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_encrypted' => UserEncryptedMoney::class.':true',
            'status' => IncomeDistributionTodoStatus::class,
            'completed_at' => 'datetime',
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

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
