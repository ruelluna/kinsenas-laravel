<?php

namespace App\Services\Savings;

use App\Models\Bank;
use App\Models\BankInstitution;

class BankPayloadMapper
{
    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     accountLabel: string|null,
     *     displayName: string,
     *     logoUrl: string|null,
     *     institutionId: string|null,
     *     institutionSlug: string|null,
     *     bankAccountGroupId: string|null,
     *     spaceRole: string|null,
     *     isActive: bool,
     * }
     */
    public static function toOption(Bank $bank): array
    {
        $bank->loadMissing('institution');

        return [
            'id' => $bank->id,
            'name' => $bank->name,
            'accountLabel' => $bank->account_label,
            'displayName' => $bank->displayLabel(),
            'logoUrl' => $bank->institution?->logo_url,
            'institutionId' => $bank->bank_institution_id,
            'institutionSlug' => $bank->institution?->slug,
            'bankAccountGroupId' => $bank->bank_account_group_id,
            'spaceRole' => $bank->space_role?->value,
            'isActive' => $bank->is_active,
        ];
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     logoUrl: string|null,
     *     type: string,
     *     features: array<string, mixed>|null,
     *     savingsSpaces: array{max: int, mainLabel: string, spaceLabelPrefix: string}|null,
     * }
     */
    public static function toInstitution(BankInstitution $institution): array
    {
        $config = $institution->savingsSpacesConfig();

        return [
            'id' => $institution->id,
            'slug' => $institution->slug,
            'name' => $institution->name,
            'logoUrl' => $institution->logo_url,
            'type' => $institution->type->value,
            'features' => $institution->features,
            'savingsSpaces' => $config !== null ? [
                'max' => $config['max'],
                'mainLabel' => $config['main_label'],
                'spaceLabelPrefix' => $config['space_label_prefix'],
            ] : null,
        ];
    }
}
