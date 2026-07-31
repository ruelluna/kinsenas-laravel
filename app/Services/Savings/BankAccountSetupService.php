<?php

namespace App\Services\Savings;

use App\Enums\BankSpaceRole;
use App\Models\Bank;
use App\Models\BankInstitution;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BankAccountSetupService
{
    /**
     * @param  list<array{label: string, enabled?: bool}>  $spaces
     * @return Collection<int, Bank>
     */
    public function createSavingsSpaces(Team $team, BankInstitution $institution, string $mainLabel, array $spaces): Collection
    {
        $config = $institution->savingsSpacesConfig();

        if ($config === null) {
            throw ValidationException::withMessages([
                'bank_institution_id' => __('This institution does not support savings spaces.'),
            ]);
        }

        $enabledSpaces = collect($spaces)
            ->filter(function (array $space): bool {
                $enabled = $space['enabled'] ?? true;

                if (is_string($enabled)) {
                    return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
                }

                return (bool) $enabled;
            })
            ->values();

        if ($enabledSpaces->count() > $config['max']) {
            throw ValidationException::withMessages([
                'spaces' => __('You can add at most :max GoSave spaces.', ['max' => $config['max']]),
            ]);
        }

        $labels = collect([trim($mainLabel)])
            ->merge($enabledSpaces->pluck('label')->map(fn (string $label) => trim($label)))
            ->filter(fn (string $label) => $label !== '');

        if ($labels->count() !== $labels->unique()->count()) {
            throw ValidationException::withMessages([
                'spaces' => __('Each savings space must have a unique name.'),
            ]);
        }

        $groupId = (string) Str::uuid7();
        $sortOrder = (int) $team->banks()->max('sort_order') + 1;
        $created = collect();

        DB::transaction(function () use ($team, $institution, $mainLabel, $enabledSpaces, $config, $groupId, &$sortOrder, &$created): void {
            $main = $team->banks()->create([
                'bank_institution_id' => $institution->id,
                'bank_account_group_id' => $groupId,
                'name' => $institution->name,
                'account_label' => trim($mainLabel) !== '' ? trim($mainLabel) : $config['main_label'],
                'space_role' => BankSpaceRole::Main,
                'sort_order' => $sortOrder++,
            ]);

            $created->push($main);

            foreach ($enabledSpaces as $index => $space) {
                $label = trim($space['label']);

                if ($label === '') {
                    continue;
                }

                $created->push($team->banks()->create([
                    'bank_institution_id' => $institution->id,
                    'bank_account_group_id' => $groupId,
                    'name' => $institution->name,
                    'account_label' => $label,
                    'space_role' => BankSpaceRole::SavingsSpace,
                    'sort_order' => $sortOrder++,
                ]));
            }
        });

        return $created;
    }
}
