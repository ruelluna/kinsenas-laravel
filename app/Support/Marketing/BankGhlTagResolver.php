<?php

namespace App\Support\Marketing;

use App\Models\BankInstitution;
use App\Models\Team;

final class BankGhlTagResolver
{
    /**
     * @return list<string>
     */
    public function tagsToAddOnBankCreated(
        BankInstitution $institution,
        bool $withSavingsSpaces,
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

        if ($withSavingsSpaces && $institution->slug === 'gotyme') {
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
        }

        if (! $team->banks()->exists()) {
            $tags[] = GhlTagCatalog::BANK_ADDED;
        }

        return array_values(array_unique($tags));
    }
}
