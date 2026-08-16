<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPostType;
use App\Enums\ContentPublishScope;
use App\Models\ContentPost;
use App\Rules\VideoEmbedUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageContent() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ContentPost $post */
        $post = $this->route('post');

        return [
            'content_series_id' => ['nullable', 'uuid', Rule::exists('content_series', 'id')],
            'episode_number' => ['nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('content_posts', 'slug')->ignore($post->id)],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'body' => ['required', 'string', 'max:100000'],
            'content_type' => ['required', Rule::enum(ContentPostType::class)],
            'publish_scope' => ['required', Rule::enum(ContentPublishScope::class)],
            'status' => ['required', Rule::enum(ContentPostStatus::class)],
            'video_embed_url' => ['nullable', 'string', 'max:2048', new VideoEmbedUrl],
            'cover_image_url' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $scope = ContentPublishScope::tryFrom((string) $this->input('publish_scope'));

            if ($scope?->isPublicTeaser() && blank($this->input('excerpt'))) {
                $validator->errors()->add('excerpt', __('An excerpt is required for external or both publish scopes.'));
            }

            if ($this->filled('content_series_id') && blank($this->input('episode_number'))) {
                $validator->errors()->add('episode_number', __('Episode number is required for series posts.'));
            }
        });
    }
}
