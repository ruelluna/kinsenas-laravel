<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPublishScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePodcastEpisodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManagePlatform() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $show = $this->route('podcastShow');

        if ($show !== null) {
            $this->merge([
                'podcast_show_id' => $show->id,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'podcast_show_id' => ['required', 'uuid', Rule::exists('podcast_shows', 'id')],
            'episode_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('podcast_episodes', 'episode_number')->where('podcast_show_id', $this->input('podcast_show_id')),
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('podcast_episodes', 'slug')],
            'excerpt' => ['nullable', 'string', 'max:5000'],
            'show_notes' => ['nullable', 'string'],
            'post_as' => ['nullable', 'string', 'max:255'],
            'audio_embed_url' => ['nullable', 'url:http,https', 'max:2048'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'publish_scope' => ['required', Rule::enum(ContentPublishScope::class)],
            'status' => ['required', Rule::enum(ContentPostStatus::class)],
        ];
    }
}
