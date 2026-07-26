<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ügyfélszerzés B) ág, Salesforce Lead Source/Campaign Influence minta
 * egyszerűsítve — lásd docs/ugyfelszerzes-terv.md 3.2. pont. A `leads.source`/
 * `contacts.source` szabad szöveges mező MELLETT (nem helyette) egy strukturált
 * kampány-lista, hogy "melyik hirdetésem térül meg" kérdésre riport is épülhessen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->nullable(); // pl. Facebook-hirdetés, hideghívás, ajánlás
            $table->date('started_at')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
