<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the public survey page', function () {
    $response = $this->get(route('survey'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('marketing/survey')
    );
});

it('allows unauthenticated access to the survey page', function () {
    $response = $this->get('/survey');

    $response->assertOk();
});
