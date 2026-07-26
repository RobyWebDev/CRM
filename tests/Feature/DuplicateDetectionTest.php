<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CRM best practice (crm_projekt.md 8. szekció) — nem blokkoló duplikátum-jelzés,
 * hogy ne vegyünk fel kétszer ugyanazt az embert. E-mail pontos egyezés VAGY
 * telefonszám formázástól független egyezés (utolsó 9 számjegy) alapján.
 */
class DuplicateDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_contact_with_an_existing_email_flags_it_as_a_duplicate_but_still_saves(): void
    {
        $user = User::factory()->create();
        $existing = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Régi', 'email' => 'teszt@pelda.hu']);

        $response = $this->actingAs($user)->post('/contacts', [
            'first_name' => 'Új',
            'email' => 'TESZT@pelda.hu',
        ]);

        $newContact = Contact::where('first_name', 'Új')->first();

        $response->assertRedirect(route('contacts.show', $newContact));
        $this->assertNotNull($newContact, 'A duplikátum-jelzés nem akadályozhatja meg a mentést.');

        $duplicates = collect(session('duplicate_contacts'));
        $this->assertTrue($duplicates->contains('id', $existing->id));
    }

    public function test_creating_a_contact_with_a_differently_formatted_matching_phone_is_flagged(): void
    {
        $user = User::factory()->create();
        $existing = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Régi', 'phone' => '+36 30 123 4567']);

        $response = $this->actingAs($user)->post('/contacts', [
            'first_name' => 'Új',
            'phone' => '06-30-123-4567',
        ]);

        $response->assertSessionHasNoErrors();
        $duplicates = collect(session('duplicate_contacts'));
        $this->assertTrue($duplicates->contains('id', $existing->id));
    }

    public function test_creating_a_contact_without_matching_email_or_phone_has_no_duplicate_warning(): void
    {
        $user = User::factory()->create();
        Contact::create(['account_id' => $user->account_id, 'first_name' => 'Más', 'email' => 'mas@pelda.hu']);

        $this->actingAs($user)->post('/contacts', [
            'first_name' => 'Teljesen Más',
            'email' => 'nem-egyezik@pelda.hu',
        ]);

        $this->assertEmpty(collect(session('duplicate_contacts')));
    }

    public function test_duplicate_check_does_not_leak_across_accounts(): void
    {
        $otherAccountUser = User::factory()->create();
        Contact::create(['account_id' => $otherAccountUser->account_id, 'first_name' => 'Másik fiók', 'email' => 'kozos@pelda.hu']);

        $user = User::factory()->create();

        $this->actingAs($user)->post('/contacts', [
            'first_name' => 'Sajátom',
            'email' => 'kozos@pelda.hu',
        ]);

        $this->assertEmpty(collect(session('duplicate_contacts')));
    }

    public function test_a_phone_number_stored_only_as_an_extra_contact_field_is_still_caught_as_a_duplicate(): void
    {
        // 2026-07-26: a duplikátum-kereső eredetileg csak a contacts.phone fő mezőt
        // nézte — miután bárki felvehet TOVÁBBI telefonszámokat is (contact_fields),
        // ez a fő mezőn kívüli adat "láthatatlan" maradt volna a duplikátum-jelzésnek.
        $user = User::factory()->create();
        $this->actingAs($user);

        $existing = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Régi']);
        $existing->contactFields()->create(['account_id' => $user->account_id, 'type' => 'phone', 'label' => 'Vezetékes', 'value' => '0612345678', 'sort_order' => 0]);

        $this->post('/contacts', [
            'first_name' => 'Új',
            'phone' => '06 1 234 5678',
        ]);

        $duplicates = collect(session('duplicate_contacts'));
        $this->assertTrue($duplicates->contains('id', $existing->id));
    }

    public function test_creating_a_lead_flags_both_similar_leads_and_existing_contacts(): void
    {
        $user = User::factory()->create();
        $existingLead = Lead::create(['account_id' => $user->account_id, 'first_name' => 'Régi Lead', 'email' => 'lead@pelda.hu', 'status' => 'new']);
        $existingContact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Régi Kontakt', 'email' => 'lead@pelda.hu']);

        $this->actingAs($user)->post('/leads', [
            'first_name' => 'Új Lead',
            'email' => 'lead@pelda.hu',
        ]);

        $duplicateLeads = collect(session('duplicate_leads'));
        $duplicateContacts = collect(session('duplicate_contacts'));

        $this->assertTrue($duplicateLeads->contains('id', $existingLead->id));
        $this->assertTrue($duplicateContacts->contains('id', $existingContact->id));
    }
}
