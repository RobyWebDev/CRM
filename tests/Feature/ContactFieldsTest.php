<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tetszőleges számú, elnevezhető elérhetőség/mező kontaktonként — Google Címtár-
 * mintára (Rob kérése, 2026-07-26): a fő e-mail/telefon/cím mező MELLETT bárki
 * hozzáadhat továbbiakat, saját elnevezéssel, vagy teljesen szabad egyedi mezőt.
 */
class ContactFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_contact_with_extra_labelled_fields_stores_them(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/contacts', [
            'first_name' => 'Bau-Haus',
            'contact_fields' => [
                ['type' => 'phone', 'label' => 'Mobil', 'value' => '06301112233'],
                ['type' => 'phone', 'label' => 'Vezetékes', 'value' => '0612345678'],
                ['type' => 'address', 'label' => 'Helyszín', 'value' => 'Budapest, Fő utca 1.'],
                ['type' => 'address', 'label' => 'Számlázási cím', 'value' => 'Budapest, Számla utca 2.'],
                ['type' => 'custom', 'label' => 'Adószám', 'value' => '12345678-1-42'],
            ],
        ]);

        $contact = Contact::where('first_name', 'Bau-Haus')->first();
        $response->assertRedirect(route('contacts.show', $contact));

        $this->assertCount(5, $contact->contactFields);
        $this->assertTrue($contact->contactFields->contains(fn ($f) => $f->label === 'Mobil' && $f->value === '06301112233'));
        $this->assertTrue($contact->contactFields->contains(fn ($f) => $f->label === 'Adószám' && $f->value === '12345678-1-42'));
    }

    public function test_unlabelled_custom_fields_are_numbered_egyedi_mezo(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Teszt']);
        $contact->contactFields()->create(['account_id' => $user->account_id, 'type' => 'custom', 'label' => null, 'value' => 'Első', 'sort_order' => 0]);
        $contact->contactFields()->create(['account_id' => $user->account_id, 'type' => 'custom', 'label' => null, 'value' => 'Második', 'sort_order' => 1]);
        $contact->contactFields()->create(['account_id' => $user->account_id, 'type' => 'phone', 'label' => null, 'value' => '0630123', 'sort_order' => 2]);

        $labelled = $contact->contactFieldsWithDisplayLabels();

        $this->assertSame('Egyedi mező 1', $labelled[0]->label);
        $this->assertSame('Egyedi mező 2', $labelled[1]->label);
        $this->assertSame('Telefon', $labelled[2]->label);
    }

    public function test_empty_field_rows_are_silently_dropped_not_validation_errors(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/contacts', [
            'first_name' => 'Üres Sor Teszt',
            'contact_fields' => [
                ['type' => 'phone', 'label' => 'Mobil', 'value' => ''],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $contact = Contact::where('first_name', 'Üres Sor Teszt')->first();
        $this->assertCount(0, $contact->contactFields);
    }

    public function test_updating_a_contact_replaces_its_extra_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Csere Teszt']);
        $contact->contactFields()->create(['account_id' => $user->account_id, 'type' => 'phone', 'label' => 'Régi', 'value' => 'régi szám', 'sort_order' => 0]);

        $this->put("/contacts/{$contact->id}", [
            'first_name' => 'Csere Teszt',
            'contact_fields' => [
                ['type' => 'phone', 'label' => 'Új', 'value' => 'új szám'],
            ],
        ]);

        $contact->refresh();
        $this->assertCount(1, $contact->contactFields);
        $this->assertSame('Új', $contact->contactFields->first()->label);
    }

    public function test_local_and_global_search_match_extra_field_values(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Kereshető Kontakt']);
        $contact->contactFields()->create(['account_id' => $user->account_id, 'type' => 'custom', 'label' => 'Adószám', 'value' => '99998888-1-42', 'sort_order' => 0]);

        $this->get('/contacts?q=99998888')->assertOk()->assertSee('Kereshető Kontakt');
        $this->get('/search?q=99998888')->assertOk()->assertSee('Kereshető Kontakt');
    }

    public function test_create_edit_and_show_pages_render_with_the_fields_editor(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Render Teszt']);
        $contact->contactFields()->create(['account_id' => $user->account_id, 'type' => 'custom', 'label' => 'Adószám', 'value' => '11112222-1-42', 'sort_order' => 0]);

        $this->get('/contacts/create')->assertOk()->assertSee('Elérhetőség/mező hozzáadása');
        $this->get("/contacts/{$contact->id}/edit")->assertOk()->assertSee('Adószám');
        $this->get("/contacts/{$contact->id}")->assertOk()->assertSee('Adószám')->assertSee('11112222-1-42');
    }

    public function test_extra_fields_do_not_leak_across_accounts(): void
    {
        $otherAccountUser = User::factory()->create();
        $otherContact = Contact::create(['account_id' => $otherAccountUser->account_id, 'first_name' => 'Másik fiók kontaktja']);
        $otherContact->contactFields()->create(['account_id' => $otherAccountUser->account_id, 'type' => 'custom', 'label' => 'Titok', 'value' => 'titkosszam123', 'sort_order' => 0]);

        $user = User::factory()->create();

        $this->actingAs($user)->get('/contacts?q=titkosszam123')->assertOk()->assertDontSee('Másik fiók kontaktja');
    }
}
