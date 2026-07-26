<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "+ Új létrehozása..." lenyíló mezők (Rob kérése, 2026-07-26) — a nagy CRM-ek
 * (Salesforce/HubSpot/Notion) mintája: ha a keresett kampány/szervezet/ajánló
 * még nincs felvéve, a lenyíló listából egy kattintással VALÓDI új rekord jön
 * létre, nem szabad szöveges duplikátum — lásd App\Support\SelectOrCreate.
 */
class SelectOrCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_choosing_new_campaign_on_a_lead_creates_a_real_campaign_record(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/leads', [
            'first_name' => 'Új Kampányos Lead',
            'campaign_id' => '__new__',
            'new_campaign_name' => 'Ősz Facebook-hirdetés',
        ]);

        $lead = Lead::where('first_name', 'Új Kampányos Lead')->first();
        $campaign = Campaign::where('name', 'Ősz Facebook-hirdetés')->first();

        $this->assertNotNull($campaign);
        $this->assertSame($campaign->id, $lead->campaign_id);
        $this->assertSame($user->account_id, $campaign->account_id);
    }

    public function test_choosing_new_campaign_twice_with_the_same_name_reuses_the_existing_campaign(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/leads', ['first_name' => 'Első Lead', 'campaign_id' => '__new__', 'new_campaign_name' => 'Ugyanaz a kampány']);
        $this->post('/leads', ['first_name' => 'Második Lead', 'campaign_id' => '__new__', 'new_campaign_name' => 'Ugyanaz a kampány']);

        $this->assertSame(1, Campaign::where('name', 'Ugyanaz a kampány')->count());
    }

    public function test_choosing_new_campaign_on_a_deal_creates_a_real_campaign_record(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pipeline = Pipeline::create(['account_id' => $user->account_id, 'name' => 'Alap', 'is_default' => true]);
        $stage = PipelineStage::create(['pipeline_id' => $pipeline->id, 'name' => 'Érdeklődés', 'sort_order' => 1]);

        $this->post('/deals', [
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'title' => 'Új kampányos üzlet',
            'campaign_id' => '__new__',
            'new_campaign_name' => 'Deal kampány',
        ]);

        $deal = Deal::where('title', 'Új kampányos üzlet')->first();
        $this->assertNotNull(Campaign::where('name', 'Deal kampány')->first());
        $this->assertSame(Campaign::where('name', 'Deal kampány')->first()->id, $deal->campaign_id);
    }

    public function test_choosing_new_organization_on_a_contact_creates_a_real_organization_record(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/contacts', [
            'first_name' => 'Kovács János',
            'organization_id' => '__new__',
            'new_organization_name' => 'Bau-Haus Kft.',
        ]);

        $contact = Contact::where('first_name', 'Kovács János')->first();
        $organization = Organization::where('name', 'Bau-Haus Kft.')->first();

        $this->assertNotNull($organization);
        $this->assertSame($organization->id, $contact->organization_id);
    }

    public function test_choosing_new_referrer_on_a_contact_creates_a_real_contact_and_links_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/contacts', [
            'first_name' => 'Ajánlott Ügyfél',
            'referred_by_contact_id' => '__new__',
            'referrer_first_name' => 'Ajánló',
            'referrer_last_name' => 'Anna',
            'referrer_phone' => '0630 111 2222',
        ]);

        $contact = Contact::where('first_name', 'Ajánlott Ügyfél')->first();
        $referrer = Contact::where('first_name', 'Ajánló')->first();

        $this->assertNotNull($referrer);
        $this->assertSame('Anna', $referrer->last_name);
        $this->assertSame($referrer->id, $contact->referred_by_contact_id);
    }

    public function test_choosing_new_referrer_without_a_name_is_silently_ignored(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/contacts', [
            'first_name' => 'Névtelen Ajánlós Kontakt',
            'referred_by_contact_id' => '__new__',
        ]);

        $response->assertSessionHasNoErrors();
        $contact = Contact::where('first_name', 'Névtelen Ajánlós Kontakt')->first();
        $this->assertNull($contact->referred_by_contact_id);
    }

    public function test_organizations_index_and_show_pages_render(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $organization = Organization::create(['account_id' => $user->account_id, 'name' => 'Render Teszt Kft.']);
        Contact::create(['account_id' => $user->account_id, 'first_name' => 'Kapcsolattartó', 'organization_id' => $organization->id]);

        $this->get('/organizations')->assertOk()->assertSee('Render Teszt Kft.');
        $this->get("/organizations/{$organization->id}")->assertOk()->assertSee('Kapcsolattartó');
    }

    public function test_new_campaign_and_new_organization_options_do_not_leak_across_accounts(): void
    {
        $otherAccountUser = User::factory()->create();
        Campaign::create(['account_id' => $otherAccountUser->account_id, 'name' => 'Közös név']);

        $user = User::factory()->create();

        $this->actingAs($user)->post('/leads', [
            'first_name' => 'Elkülönítés Teszt',
            'campaign_id' => '__new__',
            'new_campaign_name' => 'Közös név',
        ]);

        // Ugyanazzal a névvel, de a MI accountunkhoz kötve jön létre egy ÚJ kampány,
        // nem a másik fiók rekordja kerül felhasználásra (account-szűrt firstOrCreate).
        $this->assertSame(2, Campaign::withoutGlobalScopes()->where('name', 'Közös név')->count());
        $ourCampaign = Campaign::where('name', 'Közös név')->first();
        $this->assertSame($user->account_id, $ourCampaign->account_id);
    }
}
