<?php

namespace App\Models;

use Database\Factories\TeamVaultFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamVault extends Model
{
    /** @use HasFactory<TeamVaultFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id',
        'created_by_user_id',
        'wrapped_dek',
        'salt',
        'dek_version',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
