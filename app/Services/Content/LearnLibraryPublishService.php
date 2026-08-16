<?php

namespace App\Services\Content;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPublishScope;
use App\Models\PodcastEpisode;
use App\Models\PodcastShow;
use App\Models\SideHustle;
use App\Models\SideHustleCategory;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LearnLibraryPublishService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createCategory(array $attributes): SideHustleCategory
    {
        return SideHustleCategory::query()->create($this->prepareCategoryAttributes($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateCategory(SideHustleCategory $category, array $attributes): SideHustleCategory
    {
        $category->update($this->prepareCategoryAttributes($attributes, $category));

        return $category->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createSideHustle(array $attributes): SideHustle
    {
        return SideHustle::query()->create($this->prepareSideHustleAttributes($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateSideHustle(SideHustle $sideHustle, array $attributes): SideHustle
    {
        $sideHustle->update($this->prepareSideHustleAttributes($attributes, $sideHustle));

        return $sideHustle->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createPodcastShow(array $attributes): PodcastShow
    {
        return PodcastShow::query()->create($this->preparePodcastShowAttributes($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updatePodcastShow(PodcastShow $show, array $attributes): PodcastShow
    {
        $show->update($this->preparePodcastShowAttributes($attributes, $show));

        return $show->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createPodcastEpisode(array $attributes): PodcastEpisode
    {
        return PodcastEpisode::query()->create($this->preparePodcastEpisodeAttributes($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updatePodcastEpisode(PodcastEpisode $episode, array $attributes): PodcastEpisode
    {
        $episode->update($this->preparePodcastEpisodeAttributes($attributes, $episode));

        return $episode->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function prepareCategoryAttributes(array $attributes, ?SideHustleCategory $existing = null): array
    {
        if (empty($attributes['slug']) && ! empty($attributes['name'])) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function prepareSideHustleAttributes(array $attributes, ?SideHustle $existing = null): array
    {
        if (empty($attributes['slug']) && ! empty($attributes['title'])) {
            $attributes['slug'] = Str::slug($attributes['title']);
        }

        $attributes = $this->applyPublishedAt($attributes, $existing);

        $scope = ContentPublishScope::tryFrom(
            $attributes['publish_scope'] ?? $existing?->publish_scope?->value ?? ContentPublishScope::Internal->value,
        );

        if ($scope?->isPublicTeaser() && empty($attributes['excerpt']) && empty($existing?->excerpt)) {
            throw ValidationException::withMessages([
                'excerpt' => __('An excerpt is required for external or both publish scopes.'),
            ]);
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function preparePodcastShowAttributes(array $attributes, ?PodcastShow $existing = null): array
    {
        if (empty($attributes['slug']) && ! empty($attributes['title'])) {
            $attributes['slug'] = Str::slug($attributes['title']);
        }

        return $this->applyPublishedAt($attributes, $existing);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function preparePodcastEpisodeAttributes(array $attributes, ?PodcastEpisode $existing = null): array
    {
        if (empty($attributes['slug']) && ! empty($attributes['title'])) {
            $attributes['slug'] = Str::slug($attributes['title']);
        }

        $attributes = $this->applyPublishedAt($attributes, $existing);

        $scope = ContentPublishScope::tryFrom(
            $attributes['publish_scope'] ?? $existing?->publish_scope?->value ?? ContentPublishScope::Internal->value,
        );

        if ($scope?->isPublicTeaser() && empty($attributes['excerpt']) && empty($existing?->excerpt)) {
            throw ValidationException::withMessages([
                'excerpt' => __('An excerpt is required for external or both publish scopes.'),
            ]);
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function applyPublishedAt(array $attributes, ?object $existing = null): array
    {
        if (($attributes['status'] ?? $existing?->status?->value) === ContentPostStatus::Published->value
            && empty($attributes['published_at'])
            && ($existing === null || $existing->published_at === null)) {
            $attributes['published_at'] = now();
        }

        return $attributes;
    }
}
