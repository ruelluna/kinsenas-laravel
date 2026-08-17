<?php

namespace App\Models;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPostType;
use App\Enums\ContentPublishScope;
use Database\Factories\ContentPostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentPost extends Model
{
    /** @use HasFactory<ContentPostFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'content_series_id',
        'episode_number',
        'title',
        'slug',
        'excerpt',
        'body',
        'content_type',
        'publish_scope',
        'status',
        'video_embed_url',
        'cover_image_url',
        'author_id',
        'post_as',
        'metadata',
        'published_at',
        'reading_time_minutes',
    ];

    protected function casts(): array
    {
        return [
            'content_type' => ContentPostType::class,
            'publish_scope' => ContentPublishScope::class,
            'status' => ContentPostStatus::class,
            'metadata' => 'array',
            'published_at' => 'datetime',
            'episode_number' => 'integer',
            'reading_time_minutes' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(ContentSeries::class, 'content_series_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ContentPostCategory::class, 'content_post_category');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ContentReaction::class);
    }

    public function engagementEvents(): HasMany
    {
        return $this->hasMany(ContentEngagementEvent::class);
    }

    /**
     * @param  Builder<ContentPost>  $query
     * @return Builder<ContentPost>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentPostStatus::Published);
    }

    /**
     * @param  Builder<ContentPost>  $query
     * @return Builder<ContentPost>
     */
    public function scopePublicTeaser(Builder $query): Builder
    {
        return $query->published()->whereIn('publish_scope', [
            ContentPublishScope::External,
            ContentPublishScope::Both,
        ]);
    }

    /**
     * @param  Builder<ContentPost>  $query
     * @return Builder<ContentPost>
     */
    public function scopeMemberVisible(Builder $query): Builder
    {
        return $query->published();
    }

    public function isPublicTeaser(): bool
    {
        return $this->status === ContentPostStatus::Published
            && $this->publish_scope->isPublicTeaser();
    }
}
