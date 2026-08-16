<?php

namespace App\Models;

use App\Enums\ContentEngagementEventType;
use App\Enums\ContentEngagementSource;
use Database\Factories\ContentEngagementEventFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentEngagementEvent extends Model
{
    /** @use HasFactory<ContentEngagementEventFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'content_post_id',
        'user_id',
        'event_type',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => ContentEngagementEventType::class,
            'source' => ContentEngagementSource::class,
            'metadata' => 'array',
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
