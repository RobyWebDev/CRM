<?php

namespace App\Support;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * CRM best practice (crm_projekt.md 8. szekció) — nem blokkoló duplikátum-jelzés
 * kontakt/lead felvételkor, e-mail (pontos egyezés) vagy telefonszám (formázástól
 * független egyezés) alapján. Csak figyelmeztet, nem akadályozza meg a mentést —
 * lehet, hogy két különböző ember tényleg ugyanazt a céges telefonszámot használja.
 */
class DuplicateFinder
{
    /**
     * @param  class-string  $modelClass  Contact::class vagy Lead::class — mindkettőn van email/phone mező
     */
    public static function find(string $modelClass, ?string $email, ?string $phone, ?int $excludeId = null): Collection
    {
        $email = trim((string) $email);
        $phone = trim((string) $phone);
        $normalizedPhone = self::normalizePhone($phone);

        if ($email === '' && $normalizedPhone === null) {
            return new Collection();
        }

        return $modelClass::query()
            ->where(function ($query) use ($email, $normalizedPhone, $modelClass) {
                if ($email !== '') {
                    $query->orWhereRaw('LOWER(email) = ?', [Str::lower($email)]);
                }
                if ($normalizedPhone !== null) {
                    // A tárolt telefonszámból is levágjuk a formázó karaktereket (szóköz, kötőjel,
                    // zárójel, +), és az utolsó 9 számjegyet hasonlítjuk — így a "+36 30 123 4567",
                    // "06301234567", "06-30-123-4567" mind ugyanaz a szám, formázástól függetlenül.
                    // SUBSTR(..., -9) (nem RIGHT()) MySQL-en és SQLite-on (teszt-adatbázis) is működik.
                    $query->orWhereRaw(
                        "SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), -9) = ?",
                        [$normalizedPhone]
                    );
                }

                // A Kontaktoknál 2026-07-26 óta a fő email/phone mező mellett tetszőleges
                // számú, szabadon elnevezett további elérhetőség is felvehető
                // (contact_fields tábla) — enélkül egy csak ott rögzített második
                // telefonszám/e-mail láthatatlan maradna a duplikátum-keresésnek.
                if ($modelClass === Contact::class) {
                    if ($email !== '') {
                        $query->orWhereHas('contactFields', function ($fieldQuery) use ($email) {
                            $fieldQuery->where('type', 'email')->whereRaw('LOWER(value) = ?', [Str::lower($email)]);
                        });
                    }
                    if ($normalizedPhone !== null) {
                        $query->orWhereHas('contactFields', function ($fieldQuery) use ($normalizedPhone) {
                            $fieldQuery->where('type', 'phone')->whereRaw(
                                "SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(value, ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), -9) = ?",
                                [$normalizedPhone]
                            );
                        });
                    }
                }
            })
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->limit(5)
            ->get();
    }

    private static function normalizePhone(string $phone): ?string
    {
        if ($phone === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return $digits !== '' ? substr($digits, -9) : null;
    }
}
