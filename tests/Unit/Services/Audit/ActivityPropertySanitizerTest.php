<?php

use App\Enums\UserActivityAction;
use App\Services\Audit\ActivityPropertySanitizer;
use App\Services\Audit\UserActivityLogger;

it('strips forbidden financial and secret keys', function () {
    $sanitized = app(ActivityPropertySanitizer::class)->sanitize([
        'email' => 'member@example.com',
        'amount' => '50000.00',
        'category_name' => 'Everyday Fund',
        'opening_balance_encrypted' => 'secret',
    ]);

    expect($sanitized)->toBe([
        'email' => 'member@example.com',
        'category_name' => 'Everyday Fund',
    ]);
});

it('throws in testing when forbidden keys are supplied through the logger', function () {
    app(UserActivityLogger::class)->log(
        UserActivityAction::TeamCreated,
        'Created team',
        properties: ['amount' => '100.00'],
    );
})->throws(\InvalidArgumentException::class);

it('strips nested forbidden keys', function () {
    $sanitized = app(ActivityPropertySanitizer::class)->sanitize([
        'meta' => [
            'member_name' => 'Jane',
            'old' => ['amount' => '100.00'],
        ],
    ]);

    expect($sanitized)->toBe([
        'meta' => [
            'member_name' => 'Jane',
        ],
    ]);
});
