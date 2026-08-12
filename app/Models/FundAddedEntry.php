<?php

namespace App\Models;

use App\Casts\UserEncryptedMoney;
use Database\Factories\FundAddedEntryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundAddedEntry extends Model
{
    /** @use HasFactory<FundAddedEntryFactory> */
    use HasFactory, HasUuids;

    protected $table = 'fund_added_entries';

    protected $fillable = [
        'savings_plan_id',
        'category_id',
        'category_name',
        'amount_encrypted',
        'added_on',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_encrypted' => UserEncryptedMoney::class.':true',
            'added_on' => 'date',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
