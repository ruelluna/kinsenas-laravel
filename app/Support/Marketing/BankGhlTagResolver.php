<?php

namespace App\Support\Marketing;

use App\Models\Bank;
use App\Models\BankInstitution;
use App\Models\Team;

final class BankGhlTagResolver
{
    /**
     * @return list<string>
     */
    public function tagsToAddOnBankCreated(
        BankInstitution $institution,
        ?string $accountLabel,
        bool $isFirstBankOnTeam,
        bool $isFirstInstitutionOnTeam,
    ): array {
        $tags = [];

        if ($isFirstBankOnTeam) {
            $tags[] = GhlTagCatalog::BANK_ADDED;
        }

        if ($isFirstInstitutionOnTeam) {
            $tags[] = GhlTagCatalog::institutionBankAddedTag($institution->slug);
        }

        if ($institution->slug === 'gotyme' && self::isGoSaveAccountLabel($accountLabel)) {
            $tags[] = GhlTagCatalog::GOTYME_GOSAVE_SETUP;
        }

        return array_values(array_unique($tags));
    }

    /**
     * @return list<string>
     */
    public function tagsToRemoveOnBankDeleted(Team $team, BankInstitution $institution): array
    {
        $tags = [];

        if (! $team->banks()->where('bank_institution_id', $institution->id)->exists()) {
            $tags[] = GhlTagCatalog::institutionBankAddedTag($institution->slug);

            if ($institution->slug === 'gotyme') {
                $tags[] = GhlTagCatalog::GOTYME_GOSAVE_SETUP;
            }
        } elseif ($institution->slug === 'gotyme' && ! $this->teamHasGoSaveBank($team, $institution)) {
            $tags[] = GhlTagCatalog::GOTYME_GOSAVE_SETUP;
        }

        if (! $team->banks()->exists()) {
            $tags[] = GhlTagCatalog::BANK_ADDED;
        }

        return array_values(array_unique($tags));
    }

    public static function isGoSaveAccountLabel(?string $accountLabel): bool
    {
        if ($accountLabel === null) {
            return false;
        }

        $normalized = trim($accountLabel);

        if ($normalized === '') {
            return false;
        }

        return str_starts_with(strtolower($normalized), 'gosave');
    }

    private function teamHasGoSaveBank(Team $team, BankInstitution $institution): bool
    {
        return $team->banks()
            ->where('bank_institution_id', $institution->id)
            ->get()
            ->contains(fn (Bank $bank): bool => self::isGoSaveAccountLabel($bank->account_label));
    }
}
