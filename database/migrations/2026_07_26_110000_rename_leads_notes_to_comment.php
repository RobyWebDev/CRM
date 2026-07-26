<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A `leads.notes` szabad szöveges mező neve ütközött a `Lead::notes()` polimorf
 * relációval (mindkettő "notes" néven) — ez a modellen egy valódi, csendes hibát
 * okozott: ha egy Lead példány nem teljesen hidratált (pl. friss `create()` után,
 * mielőtt egy fresh SELECT visszatöltené az összes oszlopot), a `$lead->notes`
 * elérés a RELÁCIÓT adta vissza (üres Collection) a szöveg helyett. Az oszlop
 * átnevezve `comment`-re, hogy ne ütközzön a relációval (2026-07-26, valós
 * hiba tesztelés közben, lásd crm_projekt.md).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->renameColumn('notes', 'comment');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->renameColumn('comment', 'notes');
        });
    }
};
