<?php

it('shows the marketing homepage', function () {
    $page = visit('/');

    $page->assertTitleContains('Sweldo with a plan')
        ->assertSee('Sweldo with')
        ->assertSee('a plan.')
        ->assertSee('Join Beta')
        ->assertNoSmoke();
});

it('navigates to the login screen from the header', function () {
    $page = visit('/');

    $page->click('@landing-login-link')
        ->assertPathIs('/login')
        ->assertSee('Log in to your account')
        ->assertSee('Email address');
});

it('shows banks, loop, and security anchor sections', function () {
    $page = visit('/');

    $page->assertSee('Your banks, your buckets.')
        ->assertSee('Built for how Filipinos')
        ->assertSee('The Kinsenas Loop')
        ->assertSee('Choose formula')
        ->assertSee('Your numbers are for your eyes only.')
        ->assertSee('Client-side encryption')
        ->assertNoSmoke();
});
