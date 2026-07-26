<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\InsightsEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rob explicit kérése (2026-07-26): "van még olyan elem, ami szabotálja a profi
 * eredményt?" — önálló kritikai audit nyomán talált és javított hiányosságok.
 * Lásd docs/haladasi-naplo.md huszonnegyedik forduló.
 */
class BestPracticeFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_converting_a_lead_with_a_company_creates_and_links_an_organization(): void
    {
        $user = User::factory()->create();
        $lead = Lead::create(['account_id' => $user->account_id, 'first_name' => 'Kovács János', 'company' => 'Bau-Haus Kft.', 'status' => 'new']);

        $this->actingAs($user)->post("/leads/{$lead->id}/convert");

        $contact = Contact::where('first_name', 'Kovács János')->first();
        $organization = Organization::where('name', 'Bau-Haus Kft.')->first();

        $this->assertNotNull($organization, 'A lead cégneve elveszett konvertáláskor.');
        $this->assertSame($organization->id, $contact->organization_id);
    }

    public function test_converting_a_lead_reuses_an_existing_organization_case_insensitively(): void
    {
        $user = User::factory()->create();
        $existingOrg = Organization::create(['account_id' => $user->account_id, 'name' => 'Bau-Haus Kft.']);
        $lead = Lead::create(['account_id' => $user->account_id, 'first_name' => 'Második Kontakt', 'company' => 'bau-haus kft.', 'status' => 'new']);

        $this->actingAs($user)->post("/leads/{$lead->id}/convert");

        $contact = Contact::where('first_name', 'Második Kontakt')->first();
        $this->assertSame($existingOrg->id, $contact->organization_id);
        $this->assertSame(1, Organization::where('name', 'Bau-Haus Kft.')->count());
    }

    public function test_deal_inherits_organization_from_its_linked_contact(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $organization = Organization::create(['account_id' => $user->account_id, 'name' => 'Kontakt Cége']);
        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Kontakt', 'organization_id' => $organization->id]);
        $serviceType = ServiceType::create(['account_id' => $user->account_id, 'name' => 'Coaching', 'slug' => 'coaching']);
        $pipeline = Pipeline::create(['account_id' => $user->account_id, 'service_type_id' => $serviceType->id, 'name' => 'Alap', 'is_default' => true]);
        $stage = PipelineStage::create(['pipeline_id' => $pipeline->id, 'name' => 'Érdeklődés', 'sort_order' => 1]);

        $this->post('/deals', [
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'contact_id' => $contact->id,
            'title' => 'Szervezet-öröklés teszt',
        ]);

        $deal = Deal::where('title', 'Szervezet-öröklés teszt')->first();
        $this->assertSame($organization->id, $deal->organization_id);
    }

    public function test_gdpr_consent_checkbox_records_a_timestamp_and_note(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/contacts', [
            'first_name' => 'GDPR Teszt',
            'gdpr_consent_given' => '1',
            'gdpr_consent_note' => 'e-mailben',
        ]);

        $contact = Contact::where('first_name', 'GDPR Teszt')->first();
        $this->assertNotNull($contact->gdpr_consent_at);
        $this->assertSame('e-mailben', $contact->gdpr_consent_note);
    }

    public function test_gdpr_consent_timestamp_is_not_rewritten_on_every_save(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Meglévő Hozzájárulás', 'gdpr_consent_at' => now()->subDays(10)]);
        $originalTimestamp = $contact->gdpr_consent_at;

        $this->put("/contacts/{$contact->id}", [
            'first_name' => 'Meglévő Hozzájárulás',
            'gdpr_consent_given' => '1',
        ]);

        $this->assertTrue($contact->fresh()->gdpr_consent_at->equalTo($originalTimestamp));
    }

    public function test_unchecking_gdpr_consent_withdraws_it(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Visszavonás Teszt', 'gdpr_consent_at' => now()]);

        $this->put("/contacts/{$contact->id}", [
            'first_name' => 'Visszavonás Teszt',
        ]);

        $this->assertNull($contact->fresh()->gdpr_consent_at);
    }

    public function test_updating_a_contact_to_match_an_existing_email_flags_a_duplicate(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $existing = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Régi', 'email' => 'ismet@pelda.hu']);
        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Szerkesztendő']);

        $this->put("/contacts/{$contact->id}", [
            'first_name' => 'Szerkesztendő',
            'email' => 'ismet@pelda.hu',
        ]);

        $duplicates = collect(session('duplicate_contacts'));
        $this->assertTrue($duplicates->contains('id', $existing->id));
    }

    public function test_insights_flags_leads_with_an_overdue_next_step(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Lead::create([
            'account_id' => $user->account_id,
            'first_name' => 'Lejárt Lead',
            'status' => 'new',
            'next_step' => 'Hívás',
            'next_step_due_at' => now()->subDays(3),
        ]);

        $messages = collect(InsightsEngine::generate())->pluck('message')->implode(' | ');
        $this->assertStringContainsString('lejárt a következő lépés határideje', $messages);
    }
}
