<?php

namespace App\Models;

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
        'percentage',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SavingsPlan::class, 'plan_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(IncomeAllocation::class, 'category_id');
    }
}
