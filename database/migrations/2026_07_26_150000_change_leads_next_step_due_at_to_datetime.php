<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rob kérése (2026-07-26): a "következő lépés" időpontjához óra:perc is
 * tartozzon, ne csak dátum — mert ha egy találkozó van megbeszélve, azt
 * pontos időponttal kell rögzíteni, nem csak napra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dateTime('next_step_due_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->date('next_step_due_at')->nullable()->change();
        });
    }
};
