<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentPostStatus;
use App\Models\SideHustleCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSideHustleCategoryRequest extends FormRequest
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
        /** @var SideHustleCategory $category */
        $category = $this->route('side_hustle_category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('side_hustle_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(ContentPostStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
