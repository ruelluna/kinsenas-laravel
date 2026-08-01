<?php

return [
    'mode' => env('BILLING_MODE', 'live'),
    'open_beta' => [
        'launch_discount_percent' => (int) env('BILLING_OPEN_BETA_LAUNCH_DISCOUNT_PERCENT', 20),
    ],
    'default_plan_slug' => env('BILLING_DEFAULT_PLAN_SLUG', 'basic'),
    'currency' => env('BILLING_CURRENCY', 'PHP'),
    'merchant_driver' => env('BILLING_MERCHANT_DRIVER', 'manual_paymaya'),
];
