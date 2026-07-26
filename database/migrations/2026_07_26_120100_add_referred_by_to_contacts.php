<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ügyfélszerzés B) ág, Salesforce referral-partner minta egyszerűsítve — lásd
 * docs/ugyfelszerzes-terv.md 3.1. pont. Kit ajánlott melyik meglévő kontakt, hogy
 * lássuk, melyik ügyfél hozza a legtöbb új ügyfelet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('referred_by_contact_id')->nullable()->after('organization_id')
                ->constrained('contacts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_contact_id');
        });
    }
};
