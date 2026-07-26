<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Organization;
use App\Support\ContactCsvImporter;
use App\Support\CustomFieldFormHelper;
use App\Support\SelectOrCreate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * CSV-import kontaktokhoz (crm_projekt.md 3. szekció "CSV-import" elve,
 * lásd docs/csv-import-terv.md) — Rob meglévő Excel/Sheet listáinak tömeges
 * bevitele. Háromlépéses folyamat: feltöltés → mezőtérképezés + előnézet →
 * tényleges import + eredmény-riport.
 */
class ContactImportController extends Controller
{
    private const TEMP_DISK = 'local';

    private const TEMP_DIR = 'imports';

    public function create(): View
    {
        return view('contacts.import.create');
    }

    public function preview(Request $request): View
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $filename = Str::uuid().'.csv';
        $request->file('file')->storeAs(self::TEMP_DIR, $filename, self::TEMP_DISK);

        $parsed = ContactCsvImporter::parse(Storage::disk(self::TEMP_DISK)->path(self::TEMP_DIR.'/'.$filename));
        $mapping = ContactCsvImporter::guessMapping($parsed['headers']);

        return view('contacts.import.map', [
            'filename' => $filename,
            'headers' => $parsed['headers'],
            'previewRows' => array_slice($parsed['rows'], 0, 5),
            'totalRows' => count($parsed['rows']),
            'mapping' => $mapping,
            'targetFields' => ContactCsvImporter::TARGET_FIELD_LABELS,
            'customFieldDefinitions' => CustomFieldFormHelper::definitionsFor('contact', null),
        ]);
    }

    public function import(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'filename' => ['required', 'string', 'regex:/^[a-f0-9\-]+\.csv$/'],
            'mapping' => ['required', 'array'],
        ]);

        $path = self::TEMP_DIR.'/'.$data['filename'];

        if (! Storage::disk(self::TEMP_DISK)->exists($path)) {
            return redirect()->route('contacts.import.create')->with('status', 'import-file-expired');
        }

        $parsed = ContactCsvImporter::parse(Storage::disk(self::TEMP_DISK)->path($path));

        $imported = 0;
        $skippedDuplicates = 0;
        $errors = [];

        foreach ($parsed['rows'] as $index => $row) {
            $mapped = $this->applyMapping($row, $data['mapping']);

            if (trim((string) ($mapped['first_name'] ?? '')) === '') {
                $errors[] = __(':row. sor: hiányzik a keresztnév, kihagyva.', ['row' => $index + 2]);

                continue;
            }

            if (! empty($mapped['email']) && Contact::where('email', $mapped['email'])->exists()) {
                $skippedDuplicates++;

                continue;
            }

            $organizationName = $mapped['organization_name'] ?? null;
            unset($mapped['organization_name']);

            $tags = $mapped['tags'] ?? null;
            unset($mapped['tags']);

            $customFields = $mapped['custom_fields'] ?? [];
            unset($mapped['custom_fields']);

            try {
                $mapped['organization_id'] = $organizationName
                    ? SelectOrCreate::firstOrCreateByName(Organization::class, $organizationName)->id
                    : null;
                $mapped['source'] = 'csv_import';
                $mapped['custom_fields'] = $customFields;

                $contact = Contact::create($mapped);

                if ($tags) {
                    $contact->syncTagsFromString($tags);
                }

                $imported++;
            } catch (\Throwable $exception) {
                // Pl. egy nem értelmezhető dátumformátum (születésnap) a cellában — a sor
                // kimarad, de az import a többi sorral folytatódik, nem áll le teljesen.
                $errors[] = __(':row. sor: hiba történt (:message), kihagyva.', ['row' => $index + 2, 'message' => $exception->getMessage()]);
            }
        }

        Storage::disk(self::TEMP_DISK)->delete($path);

        return view('contacts.import.result', [
            'imported' => $imported,
            'skippedDuplicates' => $skippedDuplicates,
            'errors' => $errors,
        ]);
    }

    /**
     * @param  array<string, string>  $row  CSV-fejléc => cellaérték
     * @param  array<string, string>  $mapping  CSV-fejléc => célmező (vagy "custom:{field_key}")
     */
    private function applyMapping(array $row, array $mapping): array
    {
        $result = [];

        foreach ($mapping as $header => $target) {
            if (! $target || $target === '__skip__' || ! array_key_exists($header, $row)) {
                continue;
            }

            $value = $row[$header];

            if ($value === '') {
                continue;
            }

            if (str_starts_with($target, 'custom:')) {
                $fieldKey = substr($target, strlen('custom:'));
                $result['custom_fields'][$fieldKey] = $value;

                continue;
            }

            $result[$target] = $value;
        }

        return $result;
    }
}
