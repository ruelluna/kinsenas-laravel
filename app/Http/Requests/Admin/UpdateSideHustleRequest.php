<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPublishScope;
use App\Enums\SideHustleCapitalTier;
use App\Enums\SideHustleDifficulty;
use App\Models\SideHustle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSideHustleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageContent() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'skills' => $this->parseList($this->input('skills')),
            'equipment' => $this->parseList($this->input('equipment')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var SideHustle $sideHustle */
        $sideHustle = $this->route('side_hustle');

        return [
            'side_hustle_category_id' => ['required', 'uuid', Rule::exists('side_hustle_categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('side_hustles', 'slug')->ignore($sideHustle->id)],
            'excerpt' => ['nullable', 'string', 'max:5000'],
            'body' => ['required', 'string'],
            'post_as' => ['nullable', 'string', 'max:255'],
            'cover_image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'difficulty' => ['required', Rule::enum(SideHustleDifficulty::class)],
            'capital_tier' => ['required', Rule::enum(SideHustleCapitalTier::class)],
            'startup_capital_min' => ['nullable', 'integer', 'min:0'],
            'startup_capital_max' => ['nullable', 'integer', 'min:0', 'gte:startup_capital_min'],
            'time_commitment_hours_min' => ['nullable', 'integer', 'min:0', 'max:168'],
            'time_commitment_hours_max' => ['nullable', 'integer', 'min:0', 'max:168', 'gte:time_commitment_hours_min'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:255'],
            'equipment' => ['nullable', 'array'],
            'equipment.*' => ['string', 'max:255'],
            'publish_scope' => ['required', Rule::enum(ContentPublishScope::class)],
            'status' => ['required', Rule::enum(ContentPostStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return list<string>|null
     */
    private function parseList(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }
}
