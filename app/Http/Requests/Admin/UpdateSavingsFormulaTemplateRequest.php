<?php

namespace App\Http\Requests\Admin;

use App\Rules\VideoEmbedUrl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSavingsFormulaTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPlatformAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:1000'],
            'best_for' => ['nullable', 'string', 'max:5000'],
            'video_embed_url' => ['nullable', 'string', 'max:2048', new VideoEmbedUrl],
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'uuid', 'exists:savings_formula_template_categories,id'],
            'categories.*.description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
