<?php

namespace App\Casts;

use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<string|null, string|null>
 */
class UserEncryptedMoney implements CastsAttributes
{
    public function __construct(
        private ?bool $useTeamVault = null,
    ) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $dek = $this->resolveDek($model);

        if ($dek === null) {
            return null;
        }

        return app(FinancialEncryptionService::class)->decryptAmount($dek, $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = number_format((float) $value, 2, '.', '');
        $dek = $this->resolveDek($model) ?? app(VaultKeyManager::class)->requireUserDek();

        return app(FinancialEncryptionService::class)->encryptAmount($dek, $normalized);
    }

    private function resolveDek(Model $model): ?string
    {
        $manager = app(VaultKeyManager::class);

        if ($this->useTeamVault === true && method_exists($model, 'plan')) {
            $plan = $model->relationLoaded('plan') ? $model->plan : $model->plan()->first();

            if ($plan !== null && $plan->is_shared_with_team && $plan->team !== null) {
                return $manager->teamDek($plan->team) ?? $manager->userDek();
            }
        }

        if ($this->useTeamVault === true && method_exists($model, 'incomePeriod')) {
            $period = $model->relationLoaded('incomePeriod') ? $model->incomePeriod : $model->incomePeriod()->with('plan.team')->first();

            if ($period?->plan?->is_shared_with_team && $period->plan->team !== null) {
                return $manager->teamDek($period->plan->team) ?? $manager->userDek();
            }
        }

        return $manager->userDek();
    }
}
