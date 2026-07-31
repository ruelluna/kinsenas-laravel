<?php

namespace App\Models;

use App\Enums\RecipientType;
use Database\Factories\RecipientFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipient extends Model
{
    /** @use HasFactory<RecipientFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id',
        'type',
        'name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => RecipientType::class,
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }
}
