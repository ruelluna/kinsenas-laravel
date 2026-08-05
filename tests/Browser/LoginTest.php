<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the login form', function () {
    $page = visit('/login');

    $page->assertSee('Log in to your account')
        ->assertSee('Email address')
        ->assertSee('Password')
        ->assertPresent('@login-button');
});

it('authenticates a member through the browser', function () {
    $member = User::factory()->create([
        'email' => 'member@kinsenas.test',
    ]);

    $page = visit('/login');

    $page->fill('email', $member->email)
        ->fill('password', 'password')
        ->click('@login-button')
        ->assertPathEndsWith('/dashboard')
        ->assertSee('Get started');
});
