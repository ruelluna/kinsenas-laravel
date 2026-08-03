<?php

namespace App\Http\Resources;

use App\Models\FundTransfer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FundTransfer */
class FundTransferResource extends JsonResource
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
            'transferredOn' => $this->transferred_on->toDateString(),
            'fromCategoryName' => $this->fromCategory?->name,
            'toCategoryName' => $this->toCategory?->name,
            'fromCategoryId' => $this->from_category_id,
            'toCategoryId' => $this->to_category_id,
        ];
    }
}
