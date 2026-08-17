<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPostType;
use App\Enums\ContentPublishScope;
use App\Enums\PlatformRole;
use App\Http\Requests\Admin\Concerns\AuthorizesContentPost;
use App\Models\User;
use App\Rules\VideoEmbedUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentPostRequest extends FormRequest
{
    use AuthorizesContentPost;

    public function authorize(): bool
    {
        return $this->authorizeContentPostAccess();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'content_series_id' => ['nullable', 'uuid', Rule::exists('content_series', 'id')],
            'episode_number' => ['nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('content_posts', 'slug')],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'body' => ['required', 'string', 'max:100000'],
            'content_type' => ['required', Rule::enum(ContentPostType::class)],
            'publish_scope' => ['required', Rule::enum(ContentPublishScope::class)],
            'status' => ['required', Rule::enum(ContentPostStatus::class)],
            'video_embed_url' => ['nullable', 'string', 'max:2048', new VideoEmbedUrl],
            'cover_image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'post_as' => ['nullable', 'string', 'max:255'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['uuid', Rule::exists('content_post_categories', 'id')],
        ];

        if ($this->user()?->canManagePlatform()) {
            $rules['author_id'] = [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ];
        }

        return $rules;
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

            if ($this->user()?->canManagePlatform() && $this->filled('author_id')) {
                $author = User::query()->find($this->input('author_id'));

                if ($author === null || ! $author->hasAnyRole([
                    PlatformRole::Author->value,
                    PlatformRole::PlatformAdmin->value,
                ])) {
                    $validator->errors()->add('author_id', __('The selected author must have content access.'));
                }
            }
        });
    }
}
