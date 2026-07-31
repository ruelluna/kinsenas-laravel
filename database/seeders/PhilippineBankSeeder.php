<?php

namespace Database\Seeders;

use App\Enums\BankInstitutionType;
use App\Models\BankInstitution;
use App\Services\Savings\BankInstitutionLogoService;
use Illuminate\Database\Seeder;

class PhilippineBankSeeder extends Seeder
{
    public function run(): void
    {
        /** @var list<array{slug: string, name: string, type: \App\Enums\BankInstitutionType, logo_url: string}> $institutions */
        $institutions = require database_path('data/philippine-bank-institutions.php');

        $logoService = app(BankInstitutionLogoService::class);

        $bankSortOrder = 0;
        $eWalletSortOrder = 0;

        foreach ($institutions as $row) {
            $sortOrder = $row['type'] === BankInstitutionType::Bank
                ? $bankSortOrder++
                : 1000 + $eWalletSortOrder++;

            $institution = BankInstitution::query()->firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );

            $existingLogoPath = $logoService->resolveExistingLogoPath(
                $row['slug'],
                $institution->logo_path,
            );

            if ($existingLogoPath !== null) {
                if ($institution->logo_path !== $existingLogoPath) {
                    $institution->update(['logo_path' => $existingLogoPath]);
                }

                continue;
            }

            $logoPath = $logoService->ensureLogo(
                $row['slug'],
                $row['logo_url'],
                $institution->logo_path,
            );

            if ($logoPath !== null && $institution->logo_path !== $logoPath) {
                $institution->update(['logo_path' => $logoPath]);
            }

            usleep(750_000);
        }
    }
}
