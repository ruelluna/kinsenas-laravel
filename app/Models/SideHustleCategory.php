<?php

namespace App\Models;

use App\Enums\ContentPostStatus;
use Database\Factories\SideHustleCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SideHustleCategory extends Model
{
    /** @use HasFactory<SideHustleCategoryFactory> */
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

    public function sideHustles(): HasMany
    {
        return $this->hasMany(SideHustle::class);
    }

    /**
     * @param  Builder<SideHustleCategory>  $query
     * @return Builder<SideHustleCategory>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentPostStatus::Published);
    }
}
