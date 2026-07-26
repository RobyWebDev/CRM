-- CRM adatmodell — nyers MySQL DDL terv
-- Cél: amint elkészül a Laravel/Laragon környezet, ez alapján gyorsan megírhatók az Artisan migrációk.
-- Ez NEM éles futtatásra szánt fájl, hanem tervdokumentum — lásd docs/adatmodell.md a magyarázatokért.
-- Karakterkódolás mindenhol utf8mb4 (magyar ékezetek + emoji-biztos).
-- Utolsó frissítés: 2026-07-25

SET NAMES utf8mb4;

-- ============================================================
-- accounts — tenant (egy coach/webdesigner/SEO-s teljes fiókja)
-- ============================================================
CREATE TABLE accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    owner_user_id BIGINT UNSIGNED NULL,
    subscription_tier VARCHAR(50) NOT NULL DEFAULT 'free',
    locale VARCHAR(10) NOT NULL DEFAULT 'hu',
    timezone VARCHAR(64) NOT NULL DEFAULT 'Europe/Budapest',
    theme_palette VARCHAR(30) NOT NULL DEFAULT 'forest', -- forest / salesforce — lásd szinvilag-terv.md
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- users — bejelentkező személyek, account_id-hoz kötve
-- ============================================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'member', -- owner / admin / member
    is_super_admin BOOLEAN NOT NULL DEFAULT FALSE, -- csak Robnak: minden accountot lát/kezel
    locale VARCHAR(10) NOT NULL DEFAULT 'hu',
    theme_mode VARCHAR(10) NULL, -- NULL = paletta alapértelmezése, vagy 'dark'/'light' — személyes felülbírálás
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY users_account_email_unique (account_id, email),
    CONSTRAINT fk_users_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE accounts
    ADD CONSTRAINT fk_accounts_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL;

-- ============================================================
-- service_types — szabadon bővíthető szolgáltatás-típus lista
-- ============================================================
CREATE TABLE service_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(64) NULL,
    color VARCHAR(20) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY service_types_account_slug_unique (account_id, slug),
    CONSTRAINT fk_service_types_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- pipelines — szolgáltatás-típusonként testreszabható folyamat
-- ============================================================
CREATE TABLE pipelines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    service_type_id BIGINT UNSIGNED NULL, -- NULL = szolgáltatás-független, általános pipeline
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    sort_order INT NOT NULL DEFAULT 0,
    won_creates VARCHAR(20) NOT NULL DEFAULT 'project', -- project / retainer / none — mit hozzon létre automatikusan "won" dealnél
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_pipelines_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_pipelines_service_type FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- pipeline_stages — egy pipeline lépései
-- ============================================================
CREATE TABLE pipeline_stages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pipeline_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    color VARCHAR(20) NULL,
    probability TINYINT UNSIGNED NULL, -- 0-100, opcionális forecast
    is_won_stage BOOLEAN NOT NULL DEFAULT FALSE,
    is_lost_stage BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_pipeline_stages_pipeline FOREIGN KEY (pipeline_id) REFERENCES pipelines(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- organizations — cégek/szervezetek
-- ============================================================
CREATE TABLE organizations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    website VARCHAR(255) NULL,
    industry VARCHAR(255) NULL,
    custom_fields JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_organizations_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- contacts — kapcsolattartók/ügyfelek
-- ============================================================
CREATE TABLE contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NULL,
    owner_user_id BIGINT UNSIGNED NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NULL,
    job_title VARCHAR(255) NULL, -- beosztás/pozíció (2026-07-25, MiniCRM-inspiráció)
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    birthday DATE NULL,
    website VARCHAR(255) NULL,
    address TEXT NULL,
    source VARCHAR(255) NULL,
    gdpr_consent_at TIMESTAMP NULL,
    gdpr_consent_note TEXT NULL,
    custom_fields JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_contacts_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_contacts_organization FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    CONSTRAINT fk_contacts_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- tags / taggables — szabadon felvehető címkék kontaktokhoz/szervezetekhez
-- (2026-07-25, MiniCRM-inspiráció, docs/minicrm-inspiracio.md 6. pont)
-- ============================================================
CREATE TABLE tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(20) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY tags_account_name_unique (account_id, name),
    CONSTRAINT fk_tags_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE taggables (
    tag_id BIGINT UNSIGNED NOT NULL,
    taggable_type VARCHAR(255) NOT NULL,
    taggable_id BIGINT UNSIGNED NOT NULL,
    INDEX idx_taggables_taggable (taggable_type, taggable_id),
    CONSTRAINT fk_taggables_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- deals — folyamatban lévő üzletek egy pipeline-on belül
-- ============================================================
CREATE TABLE deals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    pipeline_id BIGINT UNSIGNED NOT NULL,
    pipeline_stage_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NULL,
    organization_id BIGINT UNSIGNED NULL,
    owner_user_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL, -- mit ajánlunk/tárgyalunk — a Lead project_title-jánál részletesebb, a Project description-jénél még nem végleges (2026-07-26)
    value DECIMAL(12,2) NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'HUF',
    status VARCHAR(20) NOT NULL DEFAULT 'open', -- open / won / lost
    expected_close_date DATE NULL,
    closed_at TIMESTAMP NULL,
    stage_entered_at TIMESTAMP NULL, -- mikor került a JELENLEGI lépésére — "napok a lépésben" (elakadt deal) jelzéshez
    invoice_status VARCHAR(20) NOT NULL DEFAULT 'not_issued', -- not_issued / issued / paid — MVP: csak követés, nincs tényleges számla-generálás
    lost_reason TEXT NULL, -- miért veszett el — CRM best practice, tanulság a jövőre
    custom_fields JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_deals_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_deals_pipeline FOREIGN KEY (pipeline_id) REFERENCES pipelines(id) ON DELETE RESTRICT,
    CONSTRAINT fk_deals_stage FOREIGN KEY (pipeline_stage_id) REFERENCES pipeline_stages(id) ON DELETE RESTRICT,
    CONSTRAINT fk_deals_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    CONSTRAINT fk_deals_organization FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    CONSTRAINT fk_deals_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- leads — még nem minősített érdeklődők (CRM best practice, pl. Salesforce Lead
-- objektuma) — "konvertáláskor" Contact (+ opcionálisan Deal) lesz belőle
-- ============================================================
CREATE TABLE leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    owner_user_id BIGINT UNSIGNED NULL,
    service_type_id BIGINT UNSIGNED NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    company VARCHAR(255) NULL,
    project_title VARCHAR(255) NULL, -- konkrét projekt/feladat megnevezése (2026-07-26, Rob kérése)
    source VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'new', -- new / contacted / qualified / unqualified / converted
    current_status_note TEXT NULL, -- szabad szöveg: hol tart most a projekt (2026-07-26)
    next_step TEXT NULL, -- mindig kitölthető, de nem kötelező "következő lépés" (2026-07-26)
    next_step_due_at DATE NULL, -- a következő lépés várható időpontja (2026-07-26)
    win_probability TINYINT UNSIGNED NULL, -- 0-100, "mennyire nyerhető" (korábbi neve: score, átnevezve 2026-07-26)
    comment TEXT NULL, -- szabad megjegyzés (korábbi neve: notes, átnevezve 2026-07-26, mert ütközött a Lead::notes() polimorf relációval)
    custom_fields JSON NULL,
    converted_at TIMESTAMP NULL,
    converted_contact_id BIGINT UNSIGNED NULL,
    converted_deal_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_leads_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_leads_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_leads_service_type FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE SET NULL,
    CONSTRAINT fk_leads_converted_contact FOREIGN KEY (converted_contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    CONSTRAINT fk_leads_converted_deal FOREIGN KEY (converted_deal_id) REFERENCES deals(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- projects — aktív megbízások
-- ============================================================
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    deal_id BIGINT UNSIGNED NULL,
    contact_id BIGINT UNSIGNED NULL,
    organization_id BIGINT UNSIGNED NULL,
    service_type_id BIGINT UNSIGNED NULL,
    owner_user_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active', -- active / on_hold / completed / cancelled
    start_date DATE NULL,
    due_date DATE NULL,
    budget DECIMAL(12,2) NULL,
    invoice_status VARCHAR(20) NOT NULL DEFAULT 'not_issued', -- not_issued / issued / paid — MVP: csak követés, nincs tényleges számla-generálás
    custom_fields JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_projects_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_projects_deal FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE SET NULL,
    CONSTRAINT fk_projects_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    CONSTRAINT fk_projects_organization FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    CONSTRAINT fk_projects_service_type FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE SET NULL,
    CONSTRAINT fk_projects_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- retainers — ismétlődő (havi/negyedéves) megbízások, elkülönítve az
-- egyszeri "projects" rekordoktól (pl. folyamatos marketing/SEO kezelés)
-- ============================================================
CREATE TABLE retainers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    deal_id BIGINT UNSIGNED NULL, -- melyik "megnyert" dealből lett
    contact_id BIGINT UNSIGNED NULL,
    organization_id BIGINT UNSIGNED NULL,
    service_type_id BIGINT UNSIGNED NULL,
    owner_user_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    monthly_fee DECIMAL(12,2) NULL,
    billing_cycle VARCHAR(20) NOT NULL DEFAULT 'monthly', -- monthly / quarterly / other
    billing_day TINYINT UNSIGNED NULL, -- a hónap melyik napján esedékes a számlázás
    status VARCHAR(20) NOT NULL DEFAULT 'active', -- active / paused / ended
    started_at DATE NULL,
    ended_at DATE NULL,
    custom_fields JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_retainers_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_retainers_deal FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE SET NULL,
    CONSTRAINT fk_retainers_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    CONSTRAINT fk_retainers_organization FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    CONSTRAINT fk_retainers_service_type FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE SET NULL,
    CONSTRAINT fk_retainers_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- retainer_invoices — egy retainer havi/negyedéves számlázási periódusai
-- (MVP: csak követés-státusz, nincs tényleges számla-generálás/PDF)
-- ============================================================
CREATE TABLE retainer_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    retainer_id BIGINT UNSIGNED NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    amount DECIMAL(12,2) NULL,
    invoice_status VARCHAR(20) NOT NULL DEFAULT 'not_issued', -- not_issued / issued / paid
    issued_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_retainer_invoices_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_retainer_invoices_retainer FOREIGN KEY (retainer_id) REFERENCES retainers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- tasks — feladatok, polimorf kapcsolattal (contact/deal/project)
-- ============================================================
CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    taskable_type VARCHAR(255) NOT NULL,
    taskable_id BIGINT UNSIGNED NOT NULL,
    assigned_user_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    due_date DATETIME NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open', -- open / done / cancelled
    recurrence VARCHAR(20) NULL, -- null / daily / weekly / monthly — MiniCRM-inspiráció (2026-07-25): készre jelöléskor automatikusan létrejön a következő előfordulás
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_tasks_taskable (taskable_type, taskable_id),
    CONSTRAINT fk_tasks_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_tasks_assigned FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- notes — szabad szöveges jegyzetek, polimorf kapcsolattal
-- ============================================================
CREATE TABLE notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    noteable_type VARCHAR(255) NULL, -- NULL = "saját jegyzet", nincs semmilyen rekordhoz kötve (2026-07-25)
    noteable_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_notes_noteable (noteable_type, noteable_id),
    CONSTRAINT fk_notes_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_notes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- documents — linkek szerződésekhez, ajánlatokhoz, polimorf kapcsolattal
-- ============================================================
CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    documentable_type VARCHAR(255) NOT NULL,
    documentable_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(1000) NOT NULL,
    type VARCHAR(50) NULL, -- offer / contract / other
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_documents_documentable (documentable_type, documentable_id),
    CONSTRAINT fk_documents_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- custom_field_definitions — kódolás nélküli egyedi mezők
-- ============================================================
CREATE TABLE custom_field_definitions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    service_type_id BIGINT UNSIGNED NULL, -- NULL = minden szolgáltatásra érvényes mező
    entity_type VARCHAR(50) NOT NULL, -- contact / organization / deal / project
    field_key VARCHAR(100) NOT NULL,
    label VARCHAR(255) NOT NULL,
    field_type VARCHAR(50) NOT NULL, -- text / textarea / number / date / boolean / select / multiselect / url
    options JSON NULL,
    is_required BOOLEAN NOT NULL DEFAULT FALSE,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY custom_fields_unique (account_id, entity_type, field_key),
    CONSTRAINT fk_custom_fields_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_custom_fields_service_type FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- subscriptions — jövőbeli SaaS fázis
-- ============================================================
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    tier VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active', -- active / canceled / past_due
    started_at TIMESTAMP NULL,
    renewed_at TIMESTAMP NULL,
    canceled_at TIMESTAMP NULL,
    external_ref VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_subscriptions_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- integrations — külső eszközök kapcsolatai accountonként
-- ============================================================
CREATE TABLE integrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(100) NOT NULL,
    config JSON NULL, -- alkalmazás-szinten titkosítva tárolva (Laravel encrypted cast)
    status VARCHAR(20) NOT NULL DEFAULT 'inactive', -- active / inactive / error
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_integrations_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- api_keys — accounthoz tartozó API-kulcsok külső moduloknak
-- ============================================================
CREATE TABLE api_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    scopes JSON NULL,
    last_used_at TIMESTAMP NULL,
    revoked_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_api_keys_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Megjegyzés: activity_log tábla a spatie/laravel-activitylog
-- csomag saját migrációjából jön létre, itt nincs kézzel definiálva.
-- ============================================================
