<?php

return [
    'default_plan_slug' => env('BILLING_DEFAULT_PLAN_SLUG', 'basic'),
    'currency' => env('BILLING_CURRENCY', 'PHP'),
    'merchant_driver' => env('BILLING_MERCHANT_DRIVER', 'manual_paymaya'),
];
