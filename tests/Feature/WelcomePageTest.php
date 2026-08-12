<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the welcome landing page', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('welcome')
    );
});

it('exposes open beta state on the landing page when billing mode is open beta', function () {
    config(['billing.mode' => 'open_beta']);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('welcome')
        ->where('billingMode', 'open_beta')
        ->where('openBeta.isActive', true)
    );
});

it('exposes inactive open beta on the landing page when billing mode is live', function () {
    config(['billing.mode' => 'live']);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('welcome')
        ->where('billingMode', 'live')
        ->where('openBeta.isActive', false)
    );
});
