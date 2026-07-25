<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A "Saját jegyzetek" funkcióhoz (crm_projekt.md 8. szekció, 9. pont) a notes tábla
 * noteable_type/noteable_id mezőit nullázhatóvá kell tenni, hogy egy jegyzet
 * ne csak egy konkrét rekordhoz (Contact/Deal/stb.), hanem közvetlenül egy
 * userhez is köthető legyen, semmilyen entitás nélkül.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->string('noteable_type')->nullable()->change();
            $table->unsignedBigInteger('noteable_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->string('noteable_type')->nullable(false)->change();
            $table->unsignedBigInteger('noteable_id')->nullable(false)->change();
        });
    }
};
