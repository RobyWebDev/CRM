<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CustomFieldDefinition;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;

/**
 * Az 5 induló szolgáltatás (service_types + pipelines + pipeline_stages + custom_field_definitions),
 * a docs/pipeline-sablonok.md piszkozata alapján — lásd docs/seeder-terv.md 2-3. pont.
 *
 * FONTOS: ez a tartalom Rob validálására vár (docs/pipeline-sablonok.md fejléce) — egyelőre
 * kiindulópont, kód nélkül (az admin-felület elkészültéig phpMyAdmin-on/seederen át) szabadon
 * átnevezhető/átrendezhető, ez adja a rendszer "kódolás nélküli univerzalitását".
 */
class PipelineTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $account = Account::where('slug', 'coachlab')->firstOrFail();

        $this->seedService(
            account: $account,
            name: 'Coaching',
            slug: 'coaching',
            color: '#6fd087',
            stages: [
                'Érdeklődés / első kapcsolatfelvétel',
                'Ingyenes konzultáció egyeztetve',
                'Ingyenes konzultáció megtörtént',
                'Felmérés kiküldve',
                'Felmérés kiértékelve',
                'Ajánlat kiküldve',
                'Szerződés aláírva',
                'Coaching-ülések folyamatban',
                ['name' => 'Lezárva — sikeres', 'won' => true],
                ['name' => 'Lezárva — nem lett ügyfél', 'lost' => true],
            ],
            customFields: [
                ['entity_type' => 'contact', 'field_key' => 'felmeres_pontszam', 'label' => 'Felmérés pontszám', 'field_type' => 'number'],
                ['entity_type' => 'contact', 'field_key' => 'felmeres_datum', 'label' => 'Felmérés kitöltés dátuma', 'field_type' => 'date'],
                ['entity_type' => 'deal', 'field_key' => 'coaching_tipusa', 'label' => 'Coaching típusa', 'field_type' => 'select', 'options' => ['életvezetési', 'vezetői', 'karrier', 'egyéb']],
            ],
        );

        $this->seedService(
            account: $account,
            name: 'Szervezetfejlesztés',
            slug: 'szervezetfejlesztes',
            color: '#77cce6',
            stages: [
                'Érdeklődés / megkeresés',
                'Igényfelmérő beszélgetés',
                'Szervezeti helyzetfelmérés / diagnózis',
                'Ajánlat/koncepció kidolgozása',
                'Ajánlat prezentálása',
                'Szerződéskötés',
                'Program megvalósítása',
                'Zárás és eredményértékelés',
                ['name' => 'Lezárva — sikeres', 'won' => true],
                ['name' => 'Lezárva — nem lett ügyfél', 'lost' => true],
            ],
            customFields: [
                ['entity_type' => 'organization', 'field_key' => 'szervezet_meret', 'label' => 'Szervezet mérete (létszám)', 'field_type' => 'number'],
                ['entity_type' => 'contact', 'field_key' => 'dontashozo_pozicio', 'label' => 'Döntéshozó neve/pozíciója', 'field_type' => 'text'],
                ['entity_type' => 'deal', 'field_key' => 'program_idotartam_honap', 'label' => 'Program időtartama (hónapban)', 'field_type' => 'number'],
            ],
        );

        $this->seedService(
            account: $account,
            name: 'Webdesign',
            slug: 'webdesign',
            color: '#ffc249',
            stages: [
                'Érdeklődés',
                'Igényfelmérés (briefing)',
                'Árajánlat kiküldve',
                'Ajánlat elfogadva / szerződés',
                'Design fázis',
                'Fejlesztés fázis',
                'Ügyfél review / módosítások',
                'Élesítés (launch)',
                'Utókövetés / karbantartási szerződés',
                ['name' => 'Lezárva — sikeres', 'won' => true],
                ['name' => 'Lezárva — nem lett ügyfél', 'lost' => true],
            ],
            customFields: [
                ['entity_type' => 'project', 'field_key' => 'domain_nev', 'label' => 'Domain név', 'field_type' => 'text'],
                ['entity_type' => 'project', 'field_key' => 'hosting_szolgaltato', 'label' => 'Tárhely/hosting szolgáltató', 'field_type' => 'text'],
                ['entity_type' => 'deal', 'field_key' => 'oldalak_szama', 'label' => 'Oldalak száma', 'field_type' => 'number'],
                ['entity_type' => 'deal', 'field_key' => 'cms_technologia', 'label' => 'CMS/technológia', 'field_type' => 'select', 'options' => ['WordPress', 'egyedi', 'egyéb']],
            ],
        );

        $this->seedService(
            account: $account,
            name: 'Marketing',
            slug: 'marketing',
            color: '#fb9890',
            stages: [
                'Érdeklődés',
                'Igényfelmérés / marketing audit',
                'Stratégia/ajánlat kidolgozása',
                'Ajánlat kiküldve',
                'Szerződéskötés',
                'Kampány/tevékenység beindítása',
                'Folyamatos kezelés / riportolás',
                'Megújítás vagy lezárás',
                ['name' => 'Lezárva — sikeres', 'won' => true],
                ['name' => 'Lezárva — nem lett ügyfél', 'lost' => true],
            ],
            customFields: [
                ['entity_type' => 'deal', 'field_key' => 'havi_budzse', 'label' => 'Havi büdzsé', 'field_type' => 'number'],
                ['entity_type' => 'deal', 'field_key' => 'csatornak', 'label' => 'Csatornák', 'field_type' => 'multiselect', 'options' => ['Facebook', 'Google Ads', 'Instagram', 'e-mail', 'egyéb']],
            ],
        );

        $this->seedService(
            account: $account,
            name: 'SEO',
            slug: 'seo',
            color: '#efd62f',
            stages: [
                'Érdeklődés',
                'SEO audit elkészítése',
                'Audit prezentálása + ajánlat',
                'Szerződéskötés',
                'Onpage/technikai optimalizálás',
                'Tartalom/linképítés fázis',
                'Havi riportolás / folyamatos munka',
                'Megújítás vagy lezárás',
                ['name' => 'Lezárva — sikeres', 'won' => true],
                ['name' => 'Lezárva — nem lett ügyfél', 'lost' => true],
            ],
            customFields: [
                ['entity_type' => 'project', 'field_key' => 'celkulcsszavak', 'label' => 'Célkulcsszavak', 'field_type' => 'textarea'],
                ['entity_type' => 'project', 'field_key' => 'riport_gyakorisag', 'label' => 'Havi riport gyakorisága', 'field_type' => 'select', 'options' => ['heti', 'havi', 'negyedéves']],
                ['entity_type' => 'deal', 'field_key' => 'indulo_organikus_forgalom', 'label' => 'Induló organikus forgalom', 'field_type' => 'number'],
            ],
        );
    }

    /**
     * @param  array<int, string|array{name: string, won?: bool, lost?: bool}>  $stages
     * @param  array<int, array<string, mixed>>  $customFields
     */
    private function seedService(Account $account, string $name, string $slug, string $color, array $stages, array $customFields): void
    {
        $serviceType = ServiceType::firstOrCreate(
            ['account_id' => $account->id, 'slug' => $slug],
            ['name' => $name, 'color' => $color, 'is_active' => true]
        );

        $pipeline = Pipeline::firstOrCreate(
            ['account_id' => $account->id, 'service_type_id' => $serviceType->id],
            ['name' => $name.' pipeline', 'is_default' => true]
        );

        if ($pipeline->stages()->count() === 0) {
            foreach ($stages as $index => $stage) {
                $stageName = is_array($stage) ? $stage['name'] : $stage;

                PipelineStage::create([
                    'pipeline_id' => $pipeline->id,
                    'name' => $stageName,
                    'sort_order' => $index + 1,
                    'is_won_stage' => is_array($stage) && ($stage['won'] ?? false),
                    'is_lost_stage' => is_array($stage) && ($stage['lost'] ?? false),
                ]);
            }
        }

        foreach ($customFields as $sortOrder => $field) {
            CustomFieldDefinition::firstOrCreate(
                [
                    'account_id' => $account->id,
                    'entity_type' => $field['entity_type'],
                    'field_key' => $field['field_key'],
                ],
                [
                    'service_type_id' => $serviceType->id,
                    'label' => $field['label'],
                    'field_type' => $field['field_type'],
                    'options' => $field['options'] ?? null,
                    'sort_order' => $sortOrder,
                ]
            );
        }
    }
}
