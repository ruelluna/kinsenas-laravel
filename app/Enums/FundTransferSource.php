<?php

namespace App\Enums;

enum FundTransferSource: string
{
    case User = 'user';
    case PlanSweep = 'plan_sweep';
    case Rollover = 'rollover';
    case Rebalance = 'rebalance';
}
