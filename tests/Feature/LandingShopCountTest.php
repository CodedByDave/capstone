<?php

use App\Models\Shop;
use Inertia\Testing\AssertableInertia as Assert;

it('passes the number of active shops to the landing page', function () {
    $before = Shop::where('status', 'active')->count();
    Shop::factory()->count(2)->create(['status' => 'active']);
    Shop::factory()->create(['status' => 'pending']);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->component('Landing')
        ->where('shopCount', $before + 2));
});
