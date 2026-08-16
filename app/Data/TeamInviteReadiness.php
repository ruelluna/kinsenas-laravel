<?php

namespace App\Data;

readonly class TeamInviteReadiness
{
    /**
     * @param  array<int, array{key: string, label: string, complete: bool, href: string}>  $steps
     */
    public function __construct(
        public bool $ready,
        public array $steps,
    ) {}

    /**
     * @return array{ready: bool, steps: array<int, array{key: string, label: string, complete: bool, href: string}>}
     */
    public function toArray(): array
    {
        return [
            'ready' => $this->ready,
            'steps' => $this->steps,
        ];
    }
}
