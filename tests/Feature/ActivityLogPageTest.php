<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fiók-szintű audit napló (crm_projekt.md 8. szekció, "Admin-szabadság" lista
 * 5. pontja) — a rekordonkénti aktivitás-idővonal mellé egy összesített,
 * szűrhető lista. Az activity_log tábla nincs account_id-val ellátva, a
 * tenant-elkülönítést a causer_id → account_id leképezésen keresztül végezzük.
 */
class ActivityLogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_lists_activity_caused_by_the_current_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Contact::create(['account_id' => $user->account_id, 'first_name' => 'Napló Teszt Kontakt']);

        $this->get('/activity-log')->assertOk()->assertSee('Napló Teszt Kontakt');
    }

    public function test_activity_from_another_account_is_never_shown(): void
    {
        $otherAccountUser = User::factory()->create();
        \Illuminate\Support\Facades\Auth::login($otherAccountUser);
        Contact::create(['account_id' => $otherAccountUser->account_id, 'first_name' => 'Másik Fiók Kontaktja']);
        \Illuminate\Support\Facades\Auth::logout();

        $user = User::factory()->create();

        $this->actingAs($user)->get('/activity-log')->assertOk()->assertDontSee('Másik Fiók Kontaktja');
    }

    public function test_filtering_by_subject_type_narrows_the_list(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Contact::create(['account_id' => $user->account_id, 'first_name' => 'Szűrt Kontakt']);

        $response = $this->get('/activity-log?subject_type='.urlencode(Contact::class));

        $response->assertOk()->assertSee('Szűrt Kontakt');
    }
}
