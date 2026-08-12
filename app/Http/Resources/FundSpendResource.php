<?php

namespace App\Http\Resources;

use App\Models\FundSpend;
use App\Services\Savings\FundSpendReimbursementService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FundSpend */
class FundSpendResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totals = app(FundSpendReimbursementService::class)->totalsForSpend($this->resource);

        return [
            'id' => $this->id,
            'amount' => $this->amount_encrypted,
            'description' => $this->description,
            'status' => $this->status->value,
            'spentOn' => $this->spent_on->toDateString(),
            'bankName' => $this->bank?->name,
            'categoryName' => $this->category?->name,
            'categoryId' => $this->category_id,
            'recipientId' => $this->recipient_id,
            'recipientName' => $this->recipient?->name,
            'receiptUrl' => $this->receiptImageUrl(),
            'expectsReimbursement' => $this->expects_reimbursement,
            'expectedFromRecipientId' => $this->expected_from_recipient_id,
            'expectedFromRecipientName' => $this->expectedFromRecipient?->name,
            'reimbursementStatus' => $totals['status']->value,
            'reimbursedAmount' => $totals['received'],
            'remainingOwed' => $totals['remaining'],
            'reimbursements' => $this->whenLoaded('reimbursements', fn () => $this->reimbursements->map(fn ($reimbursement) => [
                'id' => $reimbursement->id,
                'amount' => $reimbursement->amount_encrypted,
                'receivedOn' => $reimbursement->received_on->toDateString(),
                'bankName' => $reimbursement->bank?->name,
                'notes' => $reimbursement->notes,
            ])->values()->all()),
        ];
    }
}
