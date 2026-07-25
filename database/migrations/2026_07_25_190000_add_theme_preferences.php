<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A paletta (márka/vizuális identitás) account-szintű — lásd szinvilag-terv.md.
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('theme_palette', 30)->default('forest')->after('timezone'); // forest / salesforce
        });

        // A sötét/világos MÓD viszont user-szintű preferencia (egy csapaton belül eltérhet).
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_mode', 10)->nullable()->after('locale'); // null = paletta alapértelmezése, vagy 'dark'/'light'
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('theme_palette');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('theme_mode');
        });
    }
};
