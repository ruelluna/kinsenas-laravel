<?php

namespace App\Data;

use App\Enums\FinanceActivityTier;
use Carbon\CarbonInterface;

readonly class FinanceActivitySnapshot
{
    /**
     * @param  array{setup: int, recency: int, frequency: int}  $breakdown
     */
    public function __construct(
        public int $score,
        public FinanceActivityTier $tier,
        public ?CarbonInterface $lastFinanceActivityAt,
        public array $breakdown,
        public bool $incomeLocked,
    ) {}

    /**
     * @return array{
     *     score: int,
     *     tier: string,
     *     tierLabel: string,
     *     lastFinanceActivityAt: string|null,
     *     breakdown: array{setup: int, recency: int, frequency: int},
     *     incomeLocked: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'tier' => $this->tier->value,
            'tierLabel' => $this->tier->label(),
            'lastFinanceActivityAt' => $this->lastFinanceActivityAt?->toISOString(),
            'breakdown' => $this->breakdown,
            'incomeLocked' => $this->incomeLocked,
        ];
    }
}
