<?php

namespace App\Support\Content;

use App\Models\PodcastEpisode;
use App\Models\PodcastShow;
use App\Models\SideHustle;
use App\Models\SideHustleCategory;

class LearnLibraryPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function categorySummary(SideHustleCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'status' => $category->status->value,
            'sortOrder' => $category->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function categoryAdmin(SideHustleCategory $category): array
    {
        return [
            ...self::categorySummary($category),
            'sideHustlesCount' => $category->sideHustles()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function sideHustleSummary(SideHustle $hustle, bool $includeBody = false): array
    {
        $data = [
            'id' => $hustle->id,
            'title' => $hustle->title,
            'slug' => $hustle->slug,
            'excerpt' => $hustle->excerpt,
            'coverImageUrl' => $hustle->cover_image_url,
            'difficulty' => $hustle->difficulty->value,
            'difficultyLabel' => $hustle->difficulty->label(),
            'capitalTier' => $hustle->capital_tier->value,
            'capitalTierLabel' => $hustle->capital_tier->label(),
            'startupCapitalMin' => $hustle->startup_capital_min,
            'startupCapitalMax' => $hustle->startup_capital_max,
            'timeCommitmentHoursMin' => $hustle->time_commitment_hours_min,
            'timeCommitmentHoursMax' => $hustle->time_commitment_hours_max,
            'skills' => $hustle->skills ?? [],
            'equipment' => $hustle->equipment ?? [],
            'publishScope' => $hustle->publish_scope->value,
            'status' => $hustle->status->value,
            'publishedAt' => $hustle->published_at?->toIso8601String(),
            'sortOrder' => $hustle->sort_order,
            'postAs' => $hustle->post_as,
            'bylineName' => ContentByline::forOptionalPostAs($hustle->post_as),
            'category' => $hustle->relationLoaded('category') && $hustle->category
                ? self::categorySummary($hustle->category)
                : null,
        ];

        if ($includeBody) {
            $data['body'] = $hustle->body;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function sideHustleAdmin(SideHustle $hustle): array
    {
        return [
            ...self::sideHustleSummary($hustle, includeBody: true),
            'sideHustleCategoryId' => $hustle->side_hustle_category_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function podcastShowSummary(PodcastShow $show): array
    {
        return [
            'id' => $show->id,
            'title' => $show->title,
            'slug' => $show->slug,
            'description' => $show->description,
            'coverImageUrl' => $show->cover_image_url,
            'status' => $show->status->value,
            'publishedAt' => $show->published_at?->toIso8601String(),
            'sortOrder' => $show->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function podcastShowAdmin(PodcastShow $show): array
    {
        return [
            ...self::podcastShowSummary($show),
            'episodesCount' => $show->episodes()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function podcastEpisodeSummary(PodcastEpisode $episode, bool $includeShowNotes = false): array
    {
        $data = [
            'id' => $episode->id,
            'title' => $episode->title,
            'slug' => $episode->slug,
            'excerpt' => $episode->excerpt,
            'audioEmbedUrl' => $episode->audio_embed_url,
            'durationMinutes' => $episode->duration_minutes,
            'episodeNumber' => $episode->episode_number,
            'publishScope' => $episode->publish_scope->value,
            'status' => $episode->status->value,
            'publishedAt' => $episode->published_at?->toIso8601String(),
            'postAs' => $episode->post_as,
            'bylineName' => ContentByline::forOptionalPostAs($episode->post_as),
            'show' => $episode->relationLoaded('show') && $episode->show
                ? self::podcastShowSummary($episode->show)
                : null,
        ];

        if ($includeShowNotes) {
            $data['showNotes'] = $episode->show_notes;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function podcastEpisodeAdmin(PodcastEpisode $episode): array
    {
        return [
            ...self::podcastEpisodeSummary($episode, includeShowNotes: true),
            'podcastShowId' => $episode->podcast_show_id,
        ];
    }
}
