<?php

namespace App\Services\Billing;

use App\Enums\BetaAccessCodeType;
use App\Models\BetaAccessCode;
use App\Models\BetaAccessCodeBatch;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BetaAccessCodeService
{
    public function findRedeemable(string $code): ?BetaAccessCode
    {
        $normalized = BetaAccessCode::normalizeCode($code);

        if ($normalized === '') {
            return null;
        }

        $accessCode = BetaAccessCode::query()
            ->where('code', $normalized)
            ->first();

        if ($accessCode === null || ! $accessCode->isRedeemable()) {
            return null;
        }

        return $accessCode;
    }

    public function redeem(BetaAccessCode $accessCode, User $user): void
    {
        DB::transaction(function () use ($accessCode, $user) {
            $locked = BetaAccessCode::query()
                ->whereKey($accessCode->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null || ! $locked->isRedeemable()) {
                throw ValidationException::withMessages([
                    'beta_code' => __('This beta access code is invalid or no longer available.'),
                ]);
            }

            $locked->increment('redemptions_count');

            $user->forceFill(['beta_access_code_id' => $locked->id])->save();

            Log::info('Beta access code redeemed', [
                'user_id' => $user->id,
                'beta_access_code_id' => $locked->id,
                'code' => $locked->code,
            ]);
        });
    }

    public function createSharedCode(
        User $admin,
        string $code,
        string $label,
        ?int $maxUses = null,
        ?Carbon $expiresAt = null,
    ): BetaAccessCode {
        $normalized = BetaAccessCode::normalizeCode($code);

        if ($normalized === '') {
            throw ValidationException::withMessages([
                'code' => __('Enter a valid beta access code.'),
            ]);
        }

        if (BetaAccessCode::query()->where('code', $normalized)->exists()) {
            throw ValidationException::withMessages([
                'code' => __('This beta access code already exists.'),
            ]);
        }

        $batch = BetaAccessCodeBatch::query()->create([
            'name' => $label,
            'type' => BetaAccessCodeType::EventShared,
            'quantity' => 1,
            'created_by' => $admin->id,
        ]);

        $accessCode = BetaAccessCode::query()->create([
            'batch_id' => $batch->id,
            'code' => $normalized,
            'label' => $label,
            'type' => BetaAccessCodeType::EventShared,
            'max_uses' => $maxUses,
            'expires_at' => $expiresAt,
            'created_by' => $admin->id,
        ]);

        Log::info('Beta shared access code created', [
            'admin_user_id' => $admin->id,
            'beta_access_code_id' => $accessCode->id,
            'code' => $accessCode->code,
        ]);

        return $accessCode;
    }

    /**
     * @return array{0: BetaAccessCodeBatch, 1: list<BetaAccessCode>}
     */
    public function createSingleUseBatch(
        User $admin,
        string $name,
        int $quantity,
        ?Carbon $expiresAt = null,
    ): array {
        if ($quantity < 1 || $quantity > 500) {
            throw ValidationException::withMessages([
                'quantity' => __('Generate between 1 and 500 single-use codes.'),
            ]);
        }

        return DB::transaction(function () use ($admin, $name, $quantity, $expiresAt) {
            $batch = BetaAccessCodeBatch::query()->create([
                'name' => $name,
                'type' => BetaAccessCodeType::SingleUse,
                'quantity' => $quantity,
                'created_by' => $admin->id,
            ]);

            $codes = [];

            for ($i = 0; $i < $quantity; $i++) {
                $codes[] = BetaAccessCode::query()->create([
                    'batch_id' => $batch->id,
                    'code' => $this->generateUniqueSingleUseCode(),
                    'label' => $name,
                    'type' => BetaAccessCodeType::SingleUse,
                    'max_uses' => 1,
                    'expires_at' => $expiresAt,
                    'created_by' => $admin->id,
                ]);
            }

            Log::info('Beta single-use code batch created', [
                'admin_user_id' => $admin->id,
                'batch_id' => $batch->id,
                'quantity' => $quantity,
            ]);

            return [$batch, $codes];
        });
    }

    private function generateUniqueSingleUseCode(): string
    {
        do {
            $code = Str::upper(Str::substr(str_replace('-', '', (string) Str::uuid()), 0, 4).'-'.Str::substr(str_replace('-', '', (string) Str::uuid()), 0, 4));
        } while (BetaAccessCode::query()->where('code', $code)->exists());

        return $code;
    }
}
