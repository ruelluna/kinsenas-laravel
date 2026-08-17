<?php

namespace App\Http\Requests\Learn;

use App\Enums\CommunityReportReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunityPostReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::enum(CommunityReportReason::class)],
            'details' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
