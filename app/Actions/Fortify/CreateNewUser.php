<?php

namespace App\Actions\Fortify;

use App\Actions\Teams\CreateTeam;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Services\Billing\BetaApplicationService;
use App\Services\Marketing\GhlUserTagService;
use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private CreateTeam $createTeam,
        private FinancialEncryptionService $encryption,
        private VaultKeyManager $vaultKeyManager,
        private BetaApplicationService $betaApplicationService,
        private GhlUserTagService $ghlUserTagService,
    ) {
        //
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'marketing_emails_opt_in' => ['sometimes', 'boolean'],
            'beta_code' => ['nullable', 'string', 'max:32'],
        ])->validate();

        $marketingEmailsOptIn = filter_var($input['marketing_emails_opt_in'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return DB::transaction(function () use ($input, $marketingEmailsOptIn) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'marketing_emails_opt_in' => $marketingEmailsOptIn,
                'marketing_emails_opted_in_at' => $marketingEmailsOptIn ? now() : null,
            ]);

            $this->createTeam->handle($user, isPersonal: true);

            $this->betaApplicationService->applyWithOptionalCode(
                $user,
                $input['beta_code'] ?? null,
            );

            $this->ghlUserTagService->dispatch(
                $user,
                [GhlTagCatalog::KINSENAS_USER, GhlTagCatalog::REGISTERED],
                [],
                ['event' => 'registered'],
            );

            $result = $this->encryption->createUserVault($user, $input['password']);
            session(['registration.recovery_key' => $result['recovery_key']]);

            $dek = $this->encryption->unwrapDek(
                $result['vault']->wrapped_dek,
                $input['password'],
                $result['vault']->salt,
            );
            $this->vaultKeyManager->storeUserDek($dek);

            return $user;
        });
    }
}
