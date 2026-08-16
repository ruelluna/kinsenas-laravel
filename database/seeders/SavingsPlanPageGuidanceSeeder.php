<?php

namespace Database\Seeders;

use App\Models\SavingsPlanPageGuidance;
use Illuminate\Database\Seeder;

class SavingsPlanPageGuidanceSeeder extends Seeder
{
    public function run(): void
    {
        SavingsPlanPageGuidance::instance()->update([
            'chooser_intro' => 'Pick the formula that matches how you want to divide each payday. We recommend 7 Buckets for members who want the strongest savings discipline — seven buckets, every income, nothing left unassigned. Add your banks first so you can assign each fund bucket to an account after you pick a plan. You can add custom fund buckets later, but your percentage split is set when you enter your first income.',
            'chooser_video_url' => null,
            'before_choose_note' => 'You can only have one savings plan per team. Add banks under Banks in the sidebar first, then pick a formula and assign fund buckets to those accounts. If you have not entered income yet, you can go back from the plan page and choose a different formula. After you save your first income entry, percentage fund buckets lock — so pick the formula that matches your goals before entering income.',
            'after_income_rules' => 'Once any income is saved, percentage fund buckets are frozen to protect your historical breakdowns. You can still add, edit, or remove custom fund buckets and change whether the plan is shared with your team.',
            'after_income_video_url' => null,
        ]);
    }
}
