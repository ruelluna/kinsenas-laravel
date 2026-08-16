<?php

namespace App\Models;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPublishScope;
use App\Enums\ContentSeriesStatus;
use Database\Factories\ContentSeriesFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentSeries extends Model
{
    /** @use HasFactory<ContentSeriesFactory> */
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
            'status' => ContentSeriesStatus::class,
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ContentPost::class);
    }

    public function publishedPosts(): HasMany
    {
        return $this->posts()->where('status', ContentPostStatus::Published);
    }

    public function hasPublicTeaserPosts(): bool
    {
        return $this->posts()
            ->where('status', ContentPostStatus::Published)
            ->whereIn('publish_scope', [
                ContentPublishScope::External->value,
                ContentPublishScope::Both->value,
            ])
            ->exists();
    }
}
