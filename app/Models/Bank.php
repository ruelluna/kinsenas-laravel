<?php

namespace App\Models;

use Database\Factories\BankFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    /** @use HasFactory<BankFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id',
        'bank_institution_id',
        'name',
        'account_label',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(BankInstitution::class, 'bank_institution_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(SavingsCategory::class, 'bank_savings_category');
    }

    public function fundSpends(): HasMany
    {
        return $this->hasMany(FundSpend::class);
    }

    public function fundTransfers(): HasMany
    {
        return $this->hasMany(FundTransfer::class);
    }
}
