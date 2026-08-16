<?php

namespace App\Models;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPublishScope;
use Database\Factories\PodcastEpisodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PodcastEpisode extends Model
{
    /** @use HasFactory<PodcastEpisodeFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'podcast_show_id',
        'episode_number',
        'title',
        'slug',
        'excerpt',
        'show_notes',
        'post_as',
        'audio_embed_url',
        'duration_minutes',
        'publish_scope',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'publish_scope' => ContentPublishScope::class,
            'status' => ContentPostStatus::class,
            'published_at' => 'datetime',
            'episode_number' => 'integer',
            'duration_minutes' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function show(): BelongsTo
    {
        return $this->belongsTo(PodcastShow::class, 'podcast_show_id');
    }

    /**
     * @param  Builder<PodcastEpisode>  $query
     * @return Builder<PodcastEpisode>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentPostStatus::Published);
    }

    /**
     * @param  Builder<PodcastEpisode>  $query
     * @return Builder<PodcastEpisode>
     */
    public function scopePublicTeaser(Builder $query): Builder
    {
        return $query->published()->whereIn('publish_scope', [
            ContentPublishScope::External,
            ContentPublishScope::Both,
        ]);
    }

    /**
     * @param  Builder<PodcastEpisode>  $query
     * @return Builder<PodcastEpisode>
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
