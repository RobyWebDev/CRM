<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rob kérésére (2026-07-25) + MiniCRM-inspiráció (docs/minicrm-inspiracio.md 2. pont):
        // több hasznos alap-mező a kontakt-adatlapon, kód nélküli custom_fields helyett natívan,
        // mert ezek szinte minden szakmában/felhasználásban hasznosak (nem szolgáltatás-specifikusak).
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('job_title')->nullable()->after('last_name'); // beosztás/pozíció
            $table->date('birthday')->nullable()->after('phone');
            $table->string('website')->nullable()->after('birthday');
            $table->text('address')->nullable()->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['job_title', 'birthday', 'website', 'address']);
        });
    }
};
