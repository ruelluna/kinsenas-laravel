<?php

namespace App\Models;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPublishScope;
use App\Enums\SideHustleCapitalTier;
use App\Enums\SideHustleDifficulty;
use Database\Factories\SideHustleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SideHustle extends Model
{
    /** @use HasFactory<SideHustleFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'side_hustle_category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'post_as',
        'cover_image_url',
        'difficulty',
        'capital_tier',
        'startup_capital_min',
        'startup_capital_max',
        'time_commitment_hours_min',
        'time_commitment_hours_max',
        'skills',
        'equipment',
        'publish_scope',
        'status',
        'published_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'difficulty' => SideHustleDifficulty::class,
            'capital_tier' => SideHustleCapitalTier::class,
            'publish_scope' => ContentPublishScope::class,
            'status' => ContentPostStatus::class,
            'skills' => 'array',
            'equipment' => 'array',
            'published_at' => 'datetime',
            'startup_capital_min' => 'integer',
            'startup_capital_max' => 'integer',
            'time_commitment_hours_min' => 'integer',
            'time_commitment_hours_max' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SideHustleCategory::class, 'side_hustle_category_id');
    }

    /**
     * @param  Builder<SideHustle>  $query
     * @return Builder<SideHustle>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentPostStatus::Published);
    }

    /**
     * @param  Builder<SideHustle>  $query
     * @return Builder<SideHustle>
     */
    public function scopePublicTeaser(Builder $query): Builder
    {
        return $query->published()->whereIn('publish_scope', [
            ContentPublishScope::External,
            ContentPublishScope::Both,
        ]);
    }

    /**
     * @param  Builder<SideHustle>  $query
     * @return Builder<SideHustle>
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
