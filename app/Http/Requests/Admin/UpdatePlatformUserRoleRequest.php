<?php

namespace App\Http\Requests\Admin;

use App\Enums\PlatformRole;
use App\Http\Requests\Admin\Concerns\AuthorizesAdminAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformUserRoleRequest extends FormRequest
{
    use AuthorizesAdminAccess;

    public function authorize(): bool
    {
        return $this->authorizePlatformManagement();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::enum(PlatformRole::class)],
        ];
    }
}
