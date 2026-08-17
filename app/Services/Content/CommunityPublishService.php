<?php

namespace App\Services\Content;

use App\Models\CommunityCategory;
use Illuminate\Support\Str;

class CommunityPublishService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createCategory(array $attributes): CommunityCategory
    {
        return CommunityCategory::query()->create($this->prepareCategoryAttributes($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateCategory(CommunityCategory $category, array $attributes): CommunityCategory
    {
        $category->update($this->prepareCategoryAttributes($attributes, $category));

        return $category->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function prepareCategoryAttributes(array $attributes, ?CommunityCategory $existing = null): array
    {
        if (empty($attributes['slug']) && ! empty($attributes['name'])) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        return $attributes;
    }
}
