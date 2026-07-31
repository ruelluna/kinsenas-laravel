<?php

namespace App\Models;

use App\Enums\BankSpaceRole;
use Database\Factories\BankFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    /** @use HasFactory<BankFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id',
        'bank_institution_id',
        'bank_account_group_id',
        'name',
        'account_label',
        'space_role',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'space_role' => BankSpaceRole::class,
        ];
    }

    public function displayLabel(): string
    {
        if ($this->account_label !== null && $this->account_label !== '') {
            return $this->name.' — '.$this->account_label;
        }

        return $this->name;
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(BankInstitution::class, 'bank_institution_id');
    }

    public function assignedCategories(): HasMany
    {
        return $this->hasMany(SavingsCategory::class, 'bank_id');
    }

    public function fundSpends(): HasMany
    {
        return $this->hasMany(FundSpend::class);
    }

    public function outgoingFundTransfers(): HasMany
    {
        return $this->hasMany(FundTransfer::class, 'from_bank_id');
    }

    public function incomingFundTransfers(): HasMany
    {
        return $this->hasMany(FundTransfer::class, 'to_bank_id');
    }
}
