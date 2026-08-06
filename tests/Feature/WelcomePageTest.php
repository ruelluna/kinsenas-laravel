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
    $response->assertSee('Sweldo with', false);
    $response->assertSee('a plan.', false);
});

it('shows the kinsenas loop section', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('The Kinsenas Loop', false);
    $response->assertSee('Choose formula', false);
});

it('shows encryption trust messaging on the landing page', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Your numbers are for your eyes only.', false);
    $response->assertSee('Client-side encryption', false);
});

it('shows open beta pill in hero when billing mode is open beta', function () {
    config(['billing.mode' => 'open_beta']);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Now in Open Beta', false);
});
