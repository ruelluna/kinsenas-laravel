<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the welcome landing page', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('welcome')
    );
});

it('shows the hero headline on the landing page', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Sweldo ngayon. May matitira bukas.', false);
});

it('shows how it works section anchor', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Give every peso a place before it disappears.', false);
});

it('shows encryption trust messaging on the landing page', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Your income is encrypted and safe.', false);
    $response->assertSee('Only you can unlock your financial data', false);
});

it('shows sticky open beta banner when billing mode is open beta', function () {
    config(['billing.mode' => 'open_beta']);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Open beta — free access', false);
    $response->assertSee('Apply for beta access', false);
});
