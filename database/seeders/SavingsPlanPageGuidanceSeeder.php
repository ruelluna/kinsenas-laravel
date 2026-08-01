<?php

namespace Database\Seeders;

use App\Models\SavingsPlanPageGuidance;
use Illuminate\Database\Seeder;

class SavingsPlanPageGuidanceSeeder extends Seeder
{
    public function run(): void
    {
        SavingsPlanPageGuidance::query()->firstOrCreate(
            [],
            [
                'chooser_intro' => 'Choose the savings formula that fits how you want to divide your income. Add your banks first so you can assign each fund to an account after you pick a plan. You can add custom categories later, but your percentage split is set when you enter your first income.',
                'chooser_video_url' => null,
                'before_choose_note' => 'You can only have one savings plan per team. Add banks under Banks in the sidebar first, then pick a formula and assign funds to those accounts. After you save your first income entry, percentage categories lock — so pick the formula that matches your goals before entering income.',
                'after_income_rules' => 'Once any income is saved, percentage categories are frozen to protect your historical breakdowns. You can still add, edit, or remove custom categories and change whether the plan is shared with your team.',
                'after_income_video_url' => null,
            ],
        );
    }
}
