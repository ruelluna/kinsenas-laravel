<?php

namespace App\Http\Requests\Admin;

use App\Rules\VideoEmbedUrl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSavingsPlanPageGuidanceRequest extends FormRequest
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
        return [
            'chooser_intro' => ['nullable', 'string', 'max:10000'],
            'chooser_video_url' => ['nullable', 'string', 'max:2048', new VideoEmbedUrl],
            'before_choose_note' => ['nullable', 'string', 'max:10000'],
            'after_income_rules' => ['nullable', 'string', 'max:10000'],
            'after_income_video_url' => ['nullable', 'string', 'max:2048', new VideoEmbedUrl],
        ];
    }
}
