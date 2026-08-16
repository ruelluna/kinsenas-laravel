<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentSeriesStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentSeriesRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('content_series', 'slug')],
            'description' => ['nullable', 'string', 'max:5000'],
            'cover_image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'status' => ['required', Rule::enum(ContentSeriesStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
