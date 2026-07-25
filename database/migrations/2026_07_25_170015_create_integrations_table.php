<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Külső eszközök kapcsolatai accountonként (pl. a meglévő HTML-alapú
        // ajánlat-/szerződéskészítő eszköz — lásd crm_projekt.md 7. szekció).
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 100); // pl. "google_docs", "ajanlat_szerzodes_keszito"
            $table->json('config')->nullable(); // API-kulcsok/beállítások — Laravel encrypted cast
            $table->string('status', 20)->default('inactive'); // active / inactive / error
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
