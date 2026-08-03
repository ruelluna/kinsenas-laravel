<?php

namespace App\Http\Resources;

use App\Models\FundSpend;
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
        return [
            'id' => $this->id,
            'amount' => $this->amount_encrypted,
            'description' => $this->description,
            'status' => $this->status->value,
            'spentOn' => $this->spent_on->toDateString(),
            'bankName' => $this->bank?->name,
            'categoryName' => $this->category?->name,
            'categoryId' => $this->category_id,
            'receiptUrl' => $this->receiptImageUrl(),
        ];
    }
}
