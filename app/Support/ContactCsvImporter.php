<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * CSV-import kontaktokhoz — lásd docs/csv-import-terv.md. MVP-egyszerűsítés a
 * teljes tervhez képest (2026-07-26): natív PHP CSV-feldolgozás a `maatwebsite/
 * excel` csomag helyett (nincs új Composer-függőség — a terv csak javaslatként
 * említette), és szinkron feldolgozás Queue Job helyett (Rob saját, egyfelhasználós
 * használatára ez bőven elég gyors, várólista-worker üzemeltetése nélkül).
 */
class ContactCsvImporter
{
    /**
     * A CSV oszlopfejlécekhez tartozó, felismerhető magyar/angol elnevezés-változatok
     * — a mezőtérképezés ezekkel próbál automatikusan kitalálni egy ésszerű alapértelmezést,
     * amit a felhasználó a mapping-űrlapon bármikor felülbírálhat.
     */
    private const FIELD_ALIASES = [
        'first_name' => ['keresztnev', 'firstname', 'utonev', 'first'],
        'last_name' => ['vezeteknev', 'lastname', 'csaladnev', 'last'],
        'email' => ['email', 'emailcim', 'emailaddress'],
        'phone' => ['telefon', 'phone', 'telefonszam', 'mobil', 'mobile'],
        'job_title' => ['beosztas', 'jobtitle', 'pozicio', 'titulus', 'title'],
        'birthday' => ['szuletesnap', 'birthday', 'szuletesidatum', 'birthdate'],
        'website' => ['weboldal', 'website', 'honlap', 'web'],
        'address' => ['cim', 'address', 'lakcim'],
        'organization_name' => ['ceg', 'cegnev', 'szervezet', 'company', 'organization'],
        'tags' => ['cimkek', 'tags', 'cimke', 'tag'],
    ];

    public const TARGET_FIELD_LABELS = [
        'first_name' => 'Keresztnév',
        'last_name' => 'Vezetéknév',
        'email' => 'E-mail',
        'phone' => 'Telefon',
        'job_title' => 'Beosztás',
        'birthday' => 'Születésnap',
        'website' => 'Weboldal',
        'address' => 'Cím',
        'organization_name' => 'Szervezet neve',
        'tags' => 'Címkék (vesszővel elválasztva a cellában)',
    ];

    /**
     * Beolvassa a CSV-t, felismeri a kódolást (UTF-8 / Windows-1250 / ISO-8859-2) és az
     * elválasztó karaktert (vessző/pontosvessző), majd asszociatív tömbök listáját adja
     * vissza (fejléc => érték soronként).
     *
     * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}
     */
    public static function parse(string $absolutePath): array
    {
        $raw = file_get_contents($absolutePath);

        // Az mbstring csak az "ISO-8859-2" nevet ismeri fel (nem a "Windows-1250"-et),
        // de a tényleges konverzióhoz iconv-val a Windows-1250-et használjuk forrásként,
        // mert a magyar Excel-exportok szinte mindig ténylegesen CP1250-kódolásúak.
        if (mb_detect_encoding($raw, ['UTF-8', 'ISO-8859-2'], true) !== 'UTF-8') {
            $converted = @iconv('Windows-1250', 'UTF-8', $raw);
            $raw = $converted !== false ? $converted : $raw;
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($raw));
        $firstLine = $lines[0] ?? '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $headers = str_getcsv($firstLine, $delimiter);
        $headers = array_map(fn ($h) => trim((string) $h), $headers);

        $rows = [];
        foreach (array_slice($lines, 1) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $values = str_getcsv($line, $delimiter);
            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($values[$index] ?? ''));
            }
            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, string|null> fejléc => kitalált célmező (vagy null, ha nincs egyértelmű találat)
     */
    public static function guessMapping(array $headers): array
    {
        $aliasToField = [];
        foreach (self::FIELD_ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                $aliasToField[$alias] = $field;
            }
        }

        $mapping = [];
        foreach ($headers as $header) {
            $normalized = Str::of($header)->ascii()->lower()->replaceMatches('/[^a-z0-9]/', '')->toString();
            $mapping[$header] = $aliasToField[$normalized] ?? null;
        }

        return $mapping;
    }
}
