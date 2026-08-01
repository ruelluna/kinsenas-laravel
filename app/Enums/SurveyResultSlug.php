<?php

namespace App\Enums;

enum SurveyResultSlug: string
{
    case FamilyFirstPlanner = 'family-first-planner';
    case FaithGivingPlanner = 'faith-giving-planner';
    case BillsDebtOrganizer = 'bills-debt-organizer';
    case GoalBuilder = 'goal-builder';
    case TransferTracker = 'transfer-tracker';
    case DisciplineBuilder = 'discipline-builder';
    case PaydayPlanner = 'payday-planner';
}
