<?php

namespace App\Models;

use App\Enums\ContentPostStatus;
use Database\Factories\CommunityCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityCategory extends Model
{
    /** @use HasFactory<CommunityCategoryFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentPostStatus::class,
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    /**
     * @param  Builder<CommunityCategory>  $query
     * @return Builder<CommunityCategory>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentPostStatus::Published);
    }
}
