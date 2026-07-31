<?php

namespace App\Models;

use Database\Factories\UserVaultFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVault extends Model
{
    /** @use HasFactory<UserVaultFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'wrapped_dek',
        'recovery_wrapped_dek',
        'salt',
        'recovery_key_hash',
        'dek_version',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
