<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentPostStatus;
use App\Models\PodcastShow;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePodcastShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManagePlatform() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var PodcastShow $podcastShow */
        $podcastShow = $this->route('podcast_show');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('podcast_shows', 'slug')->ignore($podcastShow->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'cover_image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'status' => ['required', Rule::enum(ContentPostStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
