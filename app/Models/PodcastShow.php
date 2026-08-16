<?php

namespace App\Models;

use App\Enums\ContentPostStatus;
use Database\Factories\PodcastShowFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PodcastShow extends Model
{
    /** @use HasFactory<PodcastShowFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image_url',
        'status',
        'published_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentPostStatus::class,
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(PodcastEpisode::class);
    }

    /**
     * @param  Builder<PodcastShow>  $query
     * @return Builder<PodcastShow>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentPostStatus::Published);
    }
}
