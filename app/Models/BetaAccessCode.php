<?php

namespace App\Models;

use App\Enums\BetaAccessCodeType;
use Database\Factories\BetaAccessCodeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $batch_id
 * @property string $code
 * @property string $label
 * @property BetaAccessCodeType $type
 * @property int|null $max_uses
 * @property int $redemptions_count
 * @property Carbon|null $expires_at
 * @property bool $is_active
 * @property int $created_by
 */
class BetaAccessCode extends Model
{
    /** @use HasFactory<BetaAccessCodeFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'batch_id',
        'code',
        'label',
        'type',
        'max_uses',
        'redemptions_count',
        'expires_at',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => BetaAccessCodeType::class,
            'max_uses' => 'integer',
            'redemptions_count' => 'integer',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public static function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BetaAccessCodeBatch::class, 'batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'beta_access_code_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function remainingUses(): ?int
    {
        if ($this->max_uses === null) {
            return null;
        }

        return max(0, $this->max_uses - $this->redemptions_count);
    }

    public function isRedeemable(): bool
    {
        if (! $this->is_active || $this->isExpired()) {
            return false;
        }

        if ($this->max_uses !== null && $this->redemptions_count >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
