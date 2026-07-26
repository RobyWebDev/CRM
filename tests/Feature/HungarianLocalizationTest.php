<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rob kérése (2026-07-26): "ahol a magyar verzióban angol elnevezések vannak,
 * azt is javítsd" — a Laravel Breeze-scaffolding (auth/profil nézetek,
 * navigáció) és a keretrendszer beépített üzenetei (validáció, bejelentkezési
 * hiba, jelszó-visszaállítás, lapozás) eddig angolul jelentek meg, mert nem
 * volt `lang/hu` mappa a projektben — a `__()` hívások csak szó szerint
 * visszaadták a bennük lévő (angol) szöveget, fordítási tábla nélkül.
 */
class HungarianLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_fully_in_hungarian(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Jelszó')
            ->assertSee('Bejelentkezés')
            ->assertDontSee('Log in')
            ->assertDontSee('Remember me')
            ->assertDontSee('Forgot your password?');
    }

    public function test_register_page_is_fully_in_hungarian(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertSee('Regisztráció')
            ->assertDontSee('Register')
            ->assertDontSee('Already registered?');
    }

    public function test_profile_page_is_fully_in_hungarian(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertSee('Profil adatai')
            ->assertSee('Jelszó módosítása')
            ->assertDontSee('Profile Information')
            ->assertDontSee('Update Password')
            ->assertDontSee('Save');
    }

    public function test_navigation_shows_hungarian_labels_not_english(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk()
            ->assertSee('Irányítópult')
            ->assertSee('Kijelentkezés')
            ->assertDontSee('Dashboard')
            ->assertDontSee('Log Out')
            ->assertDontSee('>Profile<', false);
    }

    public function test_failed_validation_shows_a_hungarian_error_message(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'nem-valos-cim',
            'password' => 'x',
            'password_confirmation' => 'y',
        ]);

        $response->assertInvalid(['name' => 'kötelező']);
    }

    public function test_failed_login_shows_a_hungarian_error_message(): void
    {
        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertInvalid(['email' => 'nem egyeznek']);
    }
}
