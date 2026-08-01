<?php

namespace App\Actions\Fortify;

use App\Actions\Teams\CreateTeam;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Services\Billing\BetaApplicationService;
use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
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
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $user->markEmailAsVerified();

            $this->createTeam->handle($user, isPersonal: true);

            $this->betaApplicationService->apply($user);

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
