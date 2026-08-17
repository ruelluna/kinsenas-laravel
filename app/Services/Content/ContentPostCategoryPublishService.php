<?php

namespace App\Services\Content;

use App\Models\ContentPostCategory;
use Illuminate\Support\Str;

class ContentPostCategoryPublishService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createCategory(array $attributes): ContentPostCategory
    {
        return ContentPostCategory::query()->create($this->prepareCategoryAttributes($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateCategory(ContentPostCategory $category, array $attributes): ContentPostCategory
    {
        $category->update($this->prepareCategoryAttributes($attributes, $category));

        return $category->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function prepareCategoryAttributes(array $attributes, ?ContentPostCategory $existing = null): array
    {
        if (empty($attributes['slug']) && ! empty($attributes['name'])) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        return $attributes;
    }
}
