<?php

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;
use Database\Seeders\BillingSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('renders branded team invitation mail with logo and primary color', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => 'owner']);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => $invitedUser->email,
        'invited_by' => $owner->id,
    ]);

    $html = (new TeamInvitationNotification($invitation))
        ->toMail($invitedUser)
        ->render()
        ->toHtml();

    $logoUrl = rtrim(config('app.url'), '/').config('brand.logo.horizontal');

    expect($html)
        ->toContain($logoUrl)
        ->toContain('logo-horizontal')
        ->toContain('#1e8b75')
        ->toContain(config('app.name'));
});

it('renders branded verify email notification with logo and primary color', function () {
    $user = User::factory()->unverified()->create();

    $html = (new VerifyEmail)
        ->toMail($user)
        ->render()
        ->toHtml();

    $logoUrl = rtrim(config('app.url'), '/').config('brand.logo.horizontal');

    expect($html)
        ->toContain($logoUrl)
        ->toContain('logo-horizontal')
        ->toContain('#1e8b75')
        ->toContain(config('app.name'));
});
