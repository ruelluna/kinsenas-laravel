<?php

namespace Database\Seeders;

use App\Enums\BillingInterval;
use App\Models\PaymentMethodConfig;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanPrice;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        $basic = SubscriptionPlan::query()->firstOrCreate(
            ['slug' => 'basic'],
            [
                'name' => 'Basic',
                'trial_days' => 14,
                'features' => ['savings_plan', 'transfers', 'reports'],
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        SubscriptionPlanPrice::query()->firstOrCreate(
            ['plan_id' => $basic->id, 'interval' => BillingInterval::Monthly],
            ['amount' => 29900, 'currency' => 'PHP', 'is_active' => true],
        );

        SubscriptionPlanPrice::query()->firstOrCreate(
            ['plan_id' => $basic->id, 'interval' => BillingInterval::Yearly],
            ['amount' => 299000, 'currency' => 'PHP', 'is_active' => true],
        );

        PaymentMethodConfig::query()->firstOrCreate(
            ['provider' => 'manual_paymaya'],
            [
                'label' => 'PayMaya',
                'instructions' => 'Scan the QR code and send payment. Use your email as reference, then submit proof on the billing page.',
                'is_active' => true,
            ],
        );
    }
}
