<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ügyfélszerzés B) ág — Salesforce Lead Source/Campaign Influence minta
 * egyszerűsítve, lásd docs/ugyfelszerzes-terv.md. A legkritikusabb elvárás
 * (crm_projekt.md "Jogosultságkezelés" pont) itt is az account-elkülönítés.
 */
class CampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_can_be_created_and_is_scoped_to_the_creators_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/campaigns', [
            'name' => '2026 nyári Facebook-hirdetés',
            'type' => 'hirdetés',
            'cost' => 50000,
        ]);

        $campaign = Campaign::first();

        $response->assertRedirect(route('campaigns.show', $campaign));
        $this->assertSame($user->account_id, $campaign->account_id);
        $this->assertSame('2026 nyári Facebook-hirdetés', $campaign->name);
    }

    public function test_a_user_cannot_see_or_modify_another_accounts_campaign(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $campaign = Campaign::create([
            'account_id' => $owner->account_id,
            'name' => 'Rob privát kampánya',
        ]);

        $this->actingAs($intruder)->get("/campaigns/{$campaign->id}")->assertNotFound();
        $this->actingAs($intruder)->get("/campaigns/{$campaign->id}/edit")->assertNotFound();
        $this->actingAs($intruder)->put("/campaigns/{$campaign->id}", ['name' => 'Átírva'])->assertNotFound();
        $this->actingAs($intruder)->delete("/campaigns/{$campaign->id}")->assertNotFound();

        $this->assertSame('Rob privát kampánya', $campaign->fresh()->name);
    }

    public function test_contact_can_record_who_referred_them(): void
    {
        $user = User::factory()->create();

        $referrer = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Ajánló Anna']);

        $response = $this->actingAs($user)->post('/contacts', [
            'first_name' => 'Új',
            'last_name' => 'Ügyfél',
            'referred_by_contact_id' => $referrer->id,
        ]);

        $newContact = Contact::where('first_name', 'Új')->first();

        $response->assertRedirect(route('contacts.show', $newContact));
        $this->assertSame($referrer->id, $newContact->referred_by_contact_id);
        $this->assertTrue($referrer->refresh()->referrals->contains($newContact));
    }

    public function test_contact_cannot_be_set_as_its_own_referrer(): void
    {
        $user = User::factory()->create();
        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Önhivatkozó']);

        $response = $this->actingAs($user)->put("/contacts/{$contact->id}", [
            'first_name' => 'Önhivatkozó',
            'referred_by_contact_id' => $contact->id,
        ]);

        $response->assertSessionHasErrors('referred_by_contact_id');
    }

    public function test_campaign_pages_render_without_errors(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::create(['account_id' => $user->account_id, 'name' => 'Render-teszt kampány']);

        $this->actingAs($user)->get('/campaigns')->assertOk();
        $this->actingAs($user)->get('/campaigns/create')->assertOk();
        $this->actingAs($user)->get("/campaigns/{$campaign->id}")->assertOk();
        $this->actingAs($user)->get("/campaigns/{$campaign->id}/edit")->assertOk();
    }

    public function test_contact_and_lead_and_deal_forms_render_with_new_fields(): void
    {
        $user = User::factory()->create();
        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Forma Teszt']);

        $this->actingAs($user)->get('/contacts/create')->assertOk();
        $this->actingAs($user)->get("/contacts/{$contact->id}/edit")->assertOk();
        $this->actingAs($user)->get('/leads/create')->assertOk();
        $this->actingAs($user)->get('/deals/create')->assertOk();
    }
}
