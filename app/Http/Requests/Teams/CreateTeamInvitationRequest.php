<?php

namespace App\Http\Requests\Teams;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Rules\UniqueTeamInvitation;
use App\Services\Teams\TeamSetupService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateTeamInvitationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $team = $this->route('team');

        abort_if(! $team instanceof Team, 404);

        return [
            'email' => ['required', 'string', 'email', 'max:255', new UniqueTeamInvitation($team)],
            'role' => ['required', 'string', Rule::enum(TeamRole::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $team = $this->route('team');

            if (! $team instanceof Team) {
                return;
            }

            $user = $this->user();

            if ($user === null || app(TeamSetupService::class)->isReadyForInvites($team, $user)) {
                return;
            }

            $validator->errors()->add(
                'setup',
                __('Complete your team setup (banks, savings plan, and income) before inviting members.'),
            );
        });
    }
}
