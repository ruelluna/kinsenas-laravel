<?php

namespace App\Actions\Fortify;

use App\Actions\Teams\CreateTeam;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
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
        private SubscriptionService $subscriptionService,
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

            $this->createTeam->handle($user, $user->name."'s Team", isPersonal: true);

            $result = $this->encryption->createUserVault($user, $input['password']);
            session(['registration.recovery_key' => $result['recovery_key']]);

            $dek = $this->encryption->unwrapDek(
                $result['vault']->wrapped_dek,
                $input['password'],
                $result['vault']->salt,
            );
            $this->vaultKeyManager->storeUserDek($dek);

            $this->subscriptionService->startTrial($user);

            return $user;
        });
    }
}
