<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_terms_page_is_public(): void
    {
        $this->get('/terms')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('legal/Terms'));
    }

    public function test_privacy_page_is_public(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('legal/Privacy'));
    }
}
