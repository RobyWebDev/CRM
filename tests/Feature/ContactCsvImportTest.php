<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\CustomFieldDefinition;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * CSV-import kontaktokhoz (crm_projekt.md 3. szekció, docs/csv-import-terv.md) —
 * Rob meglévő Excel/Sheet listáinak tömeges bevitele. MVP-egyszerűsítés: natív
 * PHP CSV-feldolgozás (nincs új Composer-függőség), szinkron feldolgozás
 * (nincs Queue Job), lásd App\Support\ContactCsvImporter fejléc-megjegyzését.
 */
class ContactCsvImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A feltöltött ideiglenes CSV-k egy elkülönített, teszt-alatt automatikusan
        // eltakarított álfájlrendszerbe kerülnek — a valós storage/app-ot nem szennyezik.
        Storage::fake('local');
    }

    private function csvFile(string $content, string $name = 'contacts.csv'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $content);
    }

    public function test_uploading_a_csv_shows_a_guessed_mapping_and_preview(): void
    {
        $user = User::factory()->create();
        $csv = "keresztnev,vezeteknev,email\nJános,Kovács,janos@pelda.hu\n";

        $response = $this->actingAs($user)->post('/contacts/import/preview', [
            'file' => $this->csvFile($csv),
        ]);

        $response->assertOk()->assertSee('janos@pelda.hu');
        // A "keresztnev" fejlécnek a first_name mezőre kellett kitalálódnia.
        $response->assertSee('selected', false);
    }

    public function test_importing_mapped_rows_creates_contacts_with_organization_and_tags(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $csv = "Név,Vezetéknév,Email,Cég,Címkék\nJános,Kovács,janos@pelda.hu,Bau-Haus Kft.,\"vip,budapest\"\n";
        $preview = $this->post('/contacts/import/preview', ['file' => $this->csvFile($csv)]);
        $filename = $this->extractFilename($preview);

        $this->post('/contacts/import', [
            'filename' => $filename,
            'mapping' => [
                'Név' => 'first_name',
                'Vezetéknév' => 'last_name',
                'Email' => 'email',
                'Cég' => 'organization_name',
                'Címkék' => 'tags',
            ],
        ])->assertOk()->assertSee('1');

        $contact = Contact::where('email', 'janos@pelda.hu')->first();
        $this->assertNotNull($contact);
        $this->assertSame('Kovács', $contact->last_name);
        $this->assertSame('csv_import', $contact->source);
        $this->assertSame('Bau-Haus Kft.', $contact->organization->name);
        $this->assertTrue($contact->tags->pluck('name')->contains('vip'));
    }

    public function test_a_row_with_an_email_that_already_exists_is_skipped_as_a_duplicate(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Contact::create(['account_id' => $user->account_id, 'first_name' => 'Régi', 'email' => 'ismet@pelda.hu']);

        $csv = "Név,Email\nÚj Sor,ismet@pelda.hu\n";
        $preview = $this->post('/contacts/import/preview', ['file' => $this->csvFile($csv)]);
        $filename = $this->extractFilename($preview);

        $response = $this->post('/contacts/import', [
            'filename' => $filename,
            'mapping' => ['Név' => 'first_name', 'Email' => 'email'],
        ]);

        $response->assertOk();
        $this->assertSame(1, Contact::where('email', 'ismet@pelda.hu')->count());
    }

    public function test_a_row_missing_the_required_first_name_is_reported_as_an_error_not_a_crash(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $csv = "Név,Email\n,hianyos@pelda.hu\n";
        $preview = $this->post('/contacts/import/preview', ['file' => $this->csvFile($csv)]);
        $filename = $this->extractFilename($preview);

        $response = $this->post('/contacts/import', [
            'filename' => $filename,
            'mapping' => ['Név' => 'first_name', 'Email' => 'email'],
        ]);

        $response->assertOk();
        $this->assertNull(Contact::where('email', 'hianyos@pelda.hu')->first());
    }

    public function test_semicolon_delimited_csv_is_auto_detected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $csv = "Nev;Email\nPontosvesszos;pontosvesszo@pelda.hu\n";
        $preview = $this->post('/contacts/import/preview', ['file' => $this->csvFile($csv)]);
        $filename = $this->extractFilename($preview);

        $this->post('/contacts/import', [
            'filename' => $filename,
            'mapping' => ['Nev' => 'first_name', 'Email' => 'email'],
        ])->assertOk();

        $this->assertNotNull(Contact::where('email', 'pontosvesszo@pelda.hu')->first());
    }

    public function test_custom_field_mapping_saves_into_custom_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        CustomFieldDefinition::create([
            'account_id' => $user->account_id,
            'entity_type' => 'contact',
            'field_key' => 'ajanlo',
            'label' => 'Ajánló neve',
            'field_type' => 'text',
        ]);

        $csv = "Nev,Ajanlo oszlop\nEgyedi Mezos,Teszt Ajánló\n";
        $preview = $this->post('/contacts/import/preview', ['file' => $this->csvFile($csv)]);
        $filename = $this->extractFilename($preview);

        $this->post('/contacts/import', [
            'filename' => $filename,
            'mapping' => ['Nev' => 'first_name', 'Ajanlo oszlop' => 'custom:ajanlo'],
        ])->assertOk();

        $contact = Contact::where('first_name', 'Egyedi Mezos')->first();
        $this->assertSame('Teszt Ajánló', $contact->custom_fields['ajanlo']);
    }

    public function test_duplicate_check_is_scoped_to_the_current_account(): void
    {
        $otherAccountUser = User::factory()->create();
        Contact::create(['account_id' => $otherAccountUser->account_id, 'first_name' => 'Másik Fiók', 'email' => 'kozos@pelda.hu']);

        $user = User::factory()->create();
        $this->actingAs($user);

        $csv = "Nev,Email\nSajat Fiokom,kozos@pelda.hu\n";
        $preview = $this->post('/contacts/import/preview', ['file' => $this->csvFile($csv)]);
        $filename = $this->extractFilename($preview);

        $this->post('/contacts/import', [
            'filename' => $filename,
            'mapping' => ['Nev' => 'first_name', 'Email' => 'email'],
        ])->assertOk();

        $this->assertNotNull(Contact::where('account_id', $user->account_id)->where('email', 'kozos@pelda.hu')->first());
    }

    private function extractFilename($response): string
    {
        preg_match('/name="filename" value="([a-f0-9\-]+\.csv)"/', $response->getContent(), $matches);

        return $matches[1] ?? '';
    }
}
