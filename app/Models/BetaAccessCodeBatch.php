<?php

namespace App\Models;

use App\Enums\BetaAccessCodeType;
use Database\Factories\BetaAccessCodeBatchFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BetaAccessCodeBatch extends Model
{
    /** @use HasFactory<BetaAccessCodeBatchFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'type',
        'quantity',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => BetaAccessCodeType::class,
            'quantity' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<BetaAccessCode, $this>
     */
    public function codes(): HasMany
    {
        return $this->hasMany(BetaAccessCode::class, 'batch_id');
    }
}
