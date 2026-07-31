<?php

namespace App\Services\Savings;

use App\Enums\TransferStatus;
use App\Models\IncomePeriod;
use App\Models\Transfer;
use App\Models\User;
use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferService
{
    public function __construct(
        private VaultKeyManager $vaultKeyManager,
        private FinancialEncryptionService $encryption,
    ) {
    }

    public function create(
        IncomePeriod $period,
        string $categoryId,
        string $bankId,
        string $recipientId,
        string $amount,
        string $transferredOn,
        ?string $notes = null,
    ): Transfer {
        if (! $period->is_locked) {
            throw ValidationException::withMessages([
                'income_period' => __('Transfers require a locked income period.'),
            ]);
        }

        return Transfer::query()->create([
            'income_period_id' => $period->id,
            'category_id' => $categoryId,
            'bank_id' => $bankId,
            'recipient_id' => $recipientId,
            'amount_encrypted' => $amount,
            'status' => TransferStatus::Pending,
            'transferred_on' => $transferredOn,
            'notes' => $notes,
        ]);
    }

    public function confirm(Transfer $transfer, User $user): Transfer
    {
        if ($transfer->status === TransferStatus::Confirmed) {
            return $transfer;
        }

        $transfer->update([
            'status' => TransferStatus::Confirmed,
            'confirmed_at' => now(),
            'confirmed_by_user_id' => $user->id,
        ]);

        return $transfer->fresh(['bank', 'recipient', 'category']);
    }

    /**
     * @return array{
     *     by_bank: list<array{bank_id: string, bank_name: string, total: string}>,
     *     by_recipient: list<array{recipient_id: string, recipient_name: string, total: string}>,
     *     by_category: list<array{category_id: string, category_name: string, total: string}>
     * }
     */
    public function reportTotals(Collection $transfers): array
    {
        $dek = $this->vaultKeyManager->userDek();

        if ($dek === null) {
            return [
                'by_bank' => [],
                'by_recipient' => [],
                'by_category' => [],
            ];
        }

        $confirmed = $transfers->where('status', TransferStatus::Confirmed);

        return [
            'by_bank' => $this->aggregate($confirmed, $dek, 'bank_id', fn (Transfer $t) => $t->bank?->name ?? 'Unknown'),
            'by_recipient' => $this->aggregate($confirmed, $dek, 'recipient_id', fn (Transfer $t) => $t->recipient?->name ?? 'Unknown'),
            'by_category' => $this->aggregate($confirmed, $dek, 'category_id', fn (Transfer $t) => $t->category?->name ?? 'Unknown'),
        ];
    }

    /**
     * @param  Collection<int, Transfer>  $transfers
     * @return list<array<string, string>>
     */
    private function aggregate(Collection $transfers, string $dek, string $key, callable $labelResolver): array
    {
        return $transfers
            ->groupBy($key)
            ->map(function (Collection $group, string $id) use ($dek, $labelResolver) {
                $total = '0.00';

                foreach ($group as $transfer) {
                    $plain = $this->encryption->tryDecryptForDisplay($dek, $transfer->getRawOriginal('amount_encrypted'));

                    if ($plain !== null) {
                        $total = bcadd($total, $plain, 2);
                    }
                }

                /** @var Transfer $first */
                $first = $group->first();

                return [
                    $key => $id,
                    str_replace('_id', '_name', $key) => $labelResolver($first),
                    'total' => $total,
                ];
            })
            ->values()
            ->all();
    }
}
