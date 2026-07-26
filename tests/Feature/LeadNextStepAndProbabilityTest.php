<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Services\InsightsEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rob kérése (2026-07-26): a "következő lépés" időpontjához óra:perc is
 * tartozzon (pl. megbeszélt találkozó), és az "esély" egy kiválasztható
 * fázishoz (státusz) kötött alapértelmezett %-ot kapjon, Salesforce
 * Stage→Probability mintájára — a mező utána is szabadon felülírható.
 */
class LeadNextStepAndProbabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_step_due_at_stores_the_exact_time_not_just_the_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/leads', [
            'first_name' => 'Találkozós Lead',
            'next_step' => 'Személyes találkozó',
            'next_step_due_at' => '2026-08-01T14:30',
        ]);

        $lead = Lead::where('first_name', 'Találkozós Lead')->first();

        $this->assertSame('14:30', $lead->next_step_due_at->format('H:i'));
        $this->assertSame('2026-08-01', $lead->next_step_due_at->format('Y-m-d'));
    }

    public function test_a_meeting_scheduled_later_today_is_not_flagged_as_overdue(): void
    {
        // Ez pontosan az a hiba, amit a dátum→datetime váltás javít: korábban
        // csak a NAP számított, így egy ma késő délutánra beütemezett találkozó
        // már reggel "lejártként" jelent volna meg.
        $user = User::factory()->create();
        $this->actingAs($user);

        Lead::create([
            'account_id' => $user->account_id,
            'first_name' => 'Ma Délutáni Találkozó',
            'status' => 'new',
            'next_step' => 'Hívás',
            'next_step_due_at' => now()->addHours(3),
        ]);

        $messages = collect(InsightsEngine::generate())->pluck('message')->implode(' | ');
        $this->assertStringNotContainsString('lejárt a következő lépés határideje', $messages);
    }

    public function test_a_meeting_earlier_today_is_flagged_as_overdue(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Lead::create([
            'account_id' => $user->account_id,
            'first_name' => 'Ma Reggeli Lekéste',
            'status' => 'new',
            'next_step' => 'Hívás',
            'next_step_due_at' => now()->subHours(3),
        ]);

        $messages = collect(InsightsEngine::generate())->pluck('message')->implode(' | ');
        $this->assertStringContainsString('lejárt a következő lépés határideje', $messages);
    }

    public function test_status_default_probability_map_is_rendered_on_the_edit_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lead = Lead::create(['account_id' => $user->account_id, 'first_name' => 'Fázis Teszt', 'status' => 'new']);

        $response = $this->get("/leads/{$lead->id}/edit");

        $response->assertOk();
        // A Js::from() a biztonságos HTML-attribútumba ágyazáshoz unicode escape-pel
        // (szó szerint """) írja az idézőjeleket, JSON.parse('...') csomagolásban.
        foreach (Lead::STATUS_DEFAULT_PROBABILITY as $status => $probability) {
            $escapedQuote = chr(92).'u0022';
            $response->assertSee($escapedQuote.$status.$escapedQuote.':'.$probability, false);
        }
    }

    public function test_win_probability_can_still_be_manually_overridden_regardless_of_status_default(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lead = Lead::create(['account_id' => $user->account_id, 'first_name' => 'Felülírás Teszt', 'status' => 'new', 'win_probability' => 10]);

        $this->put("/leads/{$lead->id}", [
            'first_name' => 'Felülírás Teszt',
            'status' => 'qualified',
            'win_probability' => 42,
        ]);

        $this->assertSame(42, $lead->fresh()->win_probability);
    }
}
