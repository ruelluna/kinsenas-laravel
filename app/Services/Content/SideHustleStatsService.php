<?php

namespace App\Services\Content;

use App\Enums\ContentPostStatus;
use App\Models\SideHustle;
use App\Models\SideHustleCategory;
use Illuminate\Support\Collection;

class SideHustleStatsService
{
    /**
     * @return array{total: int, published: int, draft: int, categoryCount: int}
     */
    public function summary(): array
    {
        return [
            'total' => SideHustle::query()->count(),
            'published' => SideHustle::query()->where('status', ContentPostStatus::Published)->count(),
            'draft' => SideHustle::query()->where('status', ContentPostStatus::Draft)->count(),
            'categoryCount' => SideHustleCategory::query()->count(),
        ];
    }

    /**
     * @return Collection<int, array{name: string, slug: string, hustlesCount: int, publishedCount: int}>
     */
    public function byCategory(): Collection
    {
        return SideHustleCategory::query()
            ->withCount([
                'sideHustles',
                'sideHustles as published_side_hustles_count' => fn ($q) => $q->where('status', ContentPostStatus::Published),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (SideHustleCategory $category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'hustlesCount' => $category->side_hustles_count,
                'publishedCount' => $category->published_side_hustles_count,
            ]);
    }
}
