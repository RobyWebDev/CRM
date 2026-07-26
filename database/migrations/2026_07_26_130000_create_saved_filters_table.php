<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mentett szűrők/nézetek — Rob saját AI-javaslata (crm_projekt.md 8. szekció),
 * pl. "Forró leadjeim" névvel elmenthető egy szűrés-kombináció, hogy ne kelljen
 * mindig újra beállítani. Csak a szerzőjéhez tartozik, mint a "Jegyzeteim".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('resource'); // pl. "contacts", "leads" — melyik listaoldalra vonatkozik
            $table->string('name');
            $table->text('query_string'); // pl. "status=qualified" — a lista URL-jének lekérdezés-része
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_filters');
    }
};
