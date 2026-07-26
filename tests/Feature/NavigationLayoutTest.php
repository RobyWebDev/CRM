<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rob jelzése (2026-07-26): a felső menüsor 11 pontja nem fért el egy sorban,
 * jobbra-balra kellett görgetni. A ritkábban használt admin-/kiegészítő
 * oldalak egy "Egyéb" lenyíló menübe kerültek, a napi használatú pontok
 * (Irányítópult, Leadek, Pipeline, Kontaktok, Projektek, Retainerek)
 * maradtak közvetlenül látható, önálló linkként.
 */
class NavigationLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_nav_items_are_directly_visible_and_secondary_ones_are_grouped(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();

        // Napi használatú pontok, önálló linkként.
        foreach (['Irányítópult', 'Leadek', 'Pipeline', 'Kontaktok', 'Projektek', 'Retainerek'] as $label) {
            $response->assertSee($label);
        }

        // A ritkábban használt pontok az "Egyéb" lenyílóban vannak, de a route-jaik
        // (és a szövegük) így is jelen vannak a kimeneti HTML-ben.
        $response->assertSee('Egyéb');
        foreach (['Szervezetek', 'Kampányok', 'Jegyzeteim', 'Egyedi mezők', 'Napló'] as $label) {
            $response->assertSee($label);
        }
    }

    public function test_navigation_has_no_leftover_extra_organizations_link(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        // A navigáció asztali ÉS mobil változata is ugyanabban a HTML-válaszban
        // van (csak CSS-sel rejtve a nem releváns nézet), ezért minden pont
        // pontosan KÉTSZER szerepel — egyszer asztali, egyszer mobil linkként.
        // A "Szervezetek" a hibajavítás előtt véletlenül HÁROMSZOR szerepelt
        // volna (dupla a fő sorban/mobil listában + egyszer az "Egyéb" menüben).
        $occurrences = substr_count($response->getContent(), 'Szervezetek');
        $this->assertSame(2, $occurrences);
    }
}
