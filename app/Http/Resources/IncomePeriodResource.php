<?php

namespace App\Http\Resources;

use App\Models\IncomePeriod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin IncomePeriod */
class IncomePeriodResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->name,
            'amount' => $this->amount_encrypted,
            'receivedOn' => $this->period_start->toDateString(),
            'status' => 'confirmed',
            'allocationsLocked' => (bool) $this->is_locked,
        ];
    }
}
