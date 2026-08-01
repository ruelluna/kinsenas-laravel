<?php

namespace App\Support\Survey;

final class SurveyAnswerOptions
{
    /** @var list<string> */
    public const QUESTION_IDS = [
        'q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q7', 'q8', 'q9', 'q10',
    ];

    /** @var array<string, list<string>> */
    public const SINGLE_SELECT = [
        'q1' => ['employee', 'freelancer', 'business_owner', 'ofw', 'student', 'couple_family', 'other'],
        'q2' => ['single', 'married', 'relationship_shared', 'parent', 'supporting_family', 'living_independently', 'living_with_family'],
        'q3' => ['none', '1', '2-3', '4-5', '6+'],
        'q4' => ['pay_bills', 'send_family', 'set_savings', 'give_tithe', 'pay_debt', 'spend_first', 'no_routine'],
        'q6' => ['clear_formula', 'manual', 'sometimes', 'want_to', 'not_needed'],
        'q7' => ['unexpected_family', 'impulse_spending', 'debt', 'low_income', 'irregular_income', 'too_many_bills', 'forgetting_transfers', 'no_clear_system'],
        'q9' => ['split_income', 'track_transfers', 'remind_unpaid', 'protect_privacy', 'payday_discipline', 'family_obligations', 'plan_giving', 'save_goals'],
        'q10' => ['early_access', 'beta_tester', 'see_features', 'bank_support', 'not_interested'],
    ];

    /** @var array<string, list<string>> */
    public const MULTI_SELECT = [
        'q5' => ['bills', 'rent', 'groceries', 'family_support', 'tuition', 'medicine', 'debt', 'church_giving', 'savings', 'business_capital', 'personal_goals'],
        'q8' => ['food_delivery', 'shopping', 'online_purchases', 'games_subscriptions', 'nightlife', 'smoking_vaping', 'gambling', 'lending', 'none', 'prefer_not_to_say'],
    ];
}
