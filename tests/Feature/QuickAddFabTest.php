<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gyors-felvétel lebegő gomb (Rob saját AI-javaslata, crm_projekt.md 8. szekció) —
 * terepen, telefonon egy kattintással felvehető új lead/kontakt.
 */
class QuickAddFabTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_quick_add_button_is_visible_on_the_dashboard_for_logged_in_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee(__('Gyors felvétel'), false);
    }

    public function test_the_quick_add_button_is_not_shown_on_the_guest_login_page(): void
    {
        $this->get('/login')->assertOk()->assertDontSee(__('Gyors felvétel'));
    }
}
