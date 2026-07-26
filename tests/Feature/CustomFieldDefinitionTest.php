<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\CustomFieldDefinition;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A "kódolás nélküli univerzalitás" mechanizmusa (crm_projekt.md 3. szekció,
 * kiemelt elvárás) — a custom_field_definitions tábla/modell a projekt
 * legelejétől létezett, de eddig 0%-ban volt megvalósítva (nem volt hozzá
 * sem admin-felület, sem dinamikus form-renderelés). Lásd docs/haladasi-naplo.md
 * huszonnegyedik forduló.
 */
class CustomFieldDefinitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_custom_field_definition_for_contacts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/custom-field-definitions', [
            'entity_type' => 'contact',
            'label' => 'Felmérés pontszám',
            'field_type' => 'number',
        ]);

        $definition = CustomFieldDefinition::where('label', 'Felmérés pontszám')->first();

        $response->assertRedirect(route('custom-field-definitions.index'));
        $this->assertNotNull($definition);
        $this->assertSame('felmeres_pontszam', $definition->field_key);
        $this->assertSame($user->account_id, $definition->account_id);
    }

    public function test_field_key_collisions_are_made_unique(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/custom-field-definitions', ['entity_type' => 'contact', 'label' => 'Szint', 'field_type' => 'text']);
        $this->post('/custom-field-definitions', ['entity_type' => 'contact', 'label' => 'Szint', 'field_type' => 'text']);

        $keys = CustomFieldDefinition::where('label', 'Szint')->pluck('field_key')->sort()->values();
        $this->assertSame(['szint', 'szint_2'], $keys->all());
    }

    public function test_select_options_are_stored_as_an_array_from_comma_separated_input(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/custom-field-definitions', [
            'entity_type' => 'contact',
            'label' => 'Szint',
            'field_type' => 'select',
            'options' => 'kezdő, haladó, profi',
        ]);

        $definition = CustomFieldDefinition::where('label', 'Szint')->first();
        $this->assertSame(['kezdő', 'haladó', 'profi'], $definition->options);
    }

    public function test_a_defined_custom_field_renders_and_saves_on_the_contact_form(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        CustomFieldDefinition::create([
            'account_id' => $user->account_id,
            'entity_type' => 'contact',
            'field_key' => 'felmeres_pontszam',
            'label' => 'Felmérés pontszám',
            'field_type' => 'number',
        ]);

        $this->get('/contacts/create')->assertOk()->assertSee('Felmérés pontszám');

        $this->post('/contacts', [
            'first_name' => 'Egyedi Mező Teszt',
            'custom_fields' => ['felmeres_pontszam' => '87'],
        ]);

        $contact = Contact::where('first_name', 'Egyedi Mező Teszt')->first();
        $this->assertSame('87', $contact->custom_fields['felmeres_pontszam']);
    }

    public function test_a_required_custom_field_blocks_saving_when_empty(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        CustomFieldDefinition::create([
            'account_id' => $user->account_id,
            'entity_type' => 'contact',
            'field_key' => 'kotelezo_mezo',
            'label' => 'Kötelező mező',
            'field_type' => 'text',
            'is_required' => true,
        ]);

        $response = $this->post('/contacts', ['first_name' => 'Hiányos']);

        $response->assertSessionHasErrors('custom_fields.kotelezo_mezo');
        $this->assertNull(Contact::where('first_name', 'Hiányos')->first());
    }

    public function test_a_custom_field_scoped_to_one_service_type_only_applies_to_deals_in_that_service_type(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $coaching = ServiceType::create(['account_id' => $user->account_id, 'name' => 'Coaching', 'slug' => 'coaching']);
        $webdesign = ServiceType::create(['account_id' => $user->account_id, 'name' => 'Webdesign', 'slug' => 'webdesign']);

        CustomFieldDefinition::create([
            'account_id' => $user->account_id,
            'service_type_id' => $coaching->id,
            'entity_type' => 'deal',
            'field_key' => 'ulesszam',
            'label' => 'Ülésszám',
            'field_type' => 'number',
        ]);

        $coachingPipeline = Pipeline::create(['account_id' => $user->account_id, 'service_type_id' => $coaching->id, 'name' => 'Coaching pipeline', 'is_default' => true]);
        $coachingStage = PipelineStage::create(['pipeline_id' => $coachingPipeline->id, 'name' => 'Érdeklődés', 'sort_order' => 1]);

        $webdesignPipeline = Pipeline::create(['account_id' => $user->account_id, 'service_type_id' => $webdesign->id, 'name' => 'Webdesign pipeline', 'is_default' => true]);
        $webdesignStage = PipelineStage::create(['pipeline_id' => $webdesignPipeline->id, 'name' => 'Érdeklődés', 'sort_order' => 1]);

        $this->get("/deals/create?pipeline={$coachingPipeline->id}")->assertOk()->assertSee('Ülésszám');
        $this->get("/deals/create?pipeline={$webdesignPipeline->id}")->assertOk()->assertDontSee('Ülésszám');

        $this->post('/deals', [
            'pipeline_id' => $coachingPipeline->id,
            'pipeline_stage_id' => $coachingStage->id,
            'title' => 'Coaching üzlet',
            'custom_fields' => ['ulesszam' => '10'],
        ]);

        $deal = Deal::where('title', 'Coaching üzlet')->first();
        $this->assertSame('10', $deal->custom_fields['ulesszam']);
    }

    public function test_custom_field_definitions_do_not_leak_across_accounts(): void
    {
        $otherAccountUser = User::factory()->create();
        CustomFieldDefinition::create([
            'account_id' => $otherAccountUser->account_id,
            'entity_type' => 'contact',
            'field_key' => 'masik_fiok_mezoje',
            'label' => 'Másik fiók mezője',
            'field_type' => 'text',
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)->get('/contacts/create')->assertOk()->assertDontSee('Másik fiók mezője');
    }

    public function test_a_datetime_custom_field_renders_and_saves_with_time(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        CustomFieldDefinition::create([
            'account_id' => $user->account_id,
            'entity_type' => 'contact',
            'field_key' => 'kovetkezo_talalkozo',
            'label' => 'Következő találkozó',
            'field_type' => 'datetime',
        ]);

        $this->get('/contacts/create')->assertOk()->assertSee('Következő találkozó');

        $this->post('/contacts', [
            'first_name' => 'Datetime Mező Teszt',
            'custom_fields' => ['kovetkezo_talalkozo' => '2026-08-01T14:30'],
        ]);

        $contact = Contact::where('first_name', 'Datetime Mező Teszt')->first();
        $this->assertSame('2026-08-01T14:30', $contact->custom_fields['kovetkezo_talalkozo']);
    }

    public function test_a_text_custom_field_enforces_the_minicrm_style_character_limit(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        CustomFieldDefinition::create([
            'account_id' => $user->account_id,
            'entity_type' => 'contact',
            'field_key' => 'rovid_szoveg',
            'label' => 'Rövid szöveg mező',
            'field_type' => 'text',
        ]);

        $response = $this->post('/contacts', [
            'first_name' => 'Túl Hosszú Mező',
            'custom_fields' => ['rovid_szoveg' => str_repeat('a', 1025)],
        ]);

        $response->assertSessionHasErrors('custom_fields.rovid_szoveg');
    }

    public function test_index_create_and_edit_pages_render(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $definition = CustomFieldDefinition::create([
            'account_id' => $user->account_id,
            'entity_type' => 'contact',
            'field_key' => 'render_teszt',
            'label' => 'Render Teszt Mező',
            'field_type' => 'text',
        ]);

        $this->get('/custom-field-definitions')->assertOk()->assertSee('Render Teszt Mező');
        $this->get('/custom-field-definitions/create')->assertOk();
        $this->get("/custom-field-definitions/{$definition->id}/edit")->assertOk()->assertSee('Render Teszt Mező');
    }

    public function test_deleting_a_definition_does_not_delete_already_saved_values(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $definition = CustomFieldDefinition::create([
            'account_id' => $user->account_id,
            'entity_type' => 'contact',
            'field_key' => 'torlendo_mezo',
            'label' => 'Törlendő mező',
            'field_type' => 'text',
        ]);

        $contact = Contact::create([
            'account_id' => $user->account_id,
            'first_name' => 'Megmaradó Érték',
            'custom_fields' => ['torlendo_mezo' => 'régi érték'],
        ]);

        $this->delete("/custom-field-definitions/{$definition->id}");

        $this->assertSame('régi érték', $contact->fresh()->custom_fields['torlendo_mezo']);
    }
}
