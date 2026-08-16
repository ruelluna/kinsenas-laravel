<?php

namespace App\Models;

use App\Enums\ContentReactionType;
use Database\Factories\ContentReactionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentReaction extends Model
{
    /** @use HasFactory<ContentReactionFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'content_post_id',
        'user_id',
        'reaction_type',
    ];

    protected function casts(): array
    {
        return [
            'reaction_type' => ContentReactionType::class,
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(ContentPost::class, 'content_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
