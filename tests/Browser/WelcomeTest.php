<?php

it('shows the marketing homepage', function () {
    $page = visit('/');

    $page->assertTitleContains('Sweldo with a plan')
        ->assertSee('Not how big you save')
        ->assertSee('Log in')
        ->assertNoSmoke();
});

it('navigates to the login screen from the header', function () {
    $page = visit('/');

    $page->click('Log in')
        ->assertPathIs('/login')
        ->assertSee('Log in to your account')
        ->assertSee('Email address');
});
