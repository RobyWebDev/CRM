<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Linkek szerződésekhez/ajánlatokhoz (pl. Google Docs, vagy a meglévő HTML-alapú
        // ajánlat-/szerződéskészítő eszköz kimenete) — lásd crm_projekt.md 7. szekció.
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->morphs('documentable');
            $table->string('title');
            $table->string('url', 1000);
            $table->string('type', 50)->nullable(); // offer / contract / other
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
