<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tetszőleges számú, elnevezhető elérhetőség/mező egy kontakthoz — Google
 * Címtár-mintára (Rob kérése, 2026-07-26): a kontakt fő e-mail/telefon/cím
 * mezője MELLETT bárki hozzáadhat továbbiakat, saját elnevezéssel (pl.
 * "Mobil"/"Vezetékes", "Helyszín"/"Számlázási cím"), sőt teljesen szabad,
 * egyedi mezőket is (pl. "Adószám"), amíg nincs elnevezve "Egyedi mező N"
 * néven jelenik meg (lásd Contact::contactFieldsWithDisplayLabels()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // email / phone / address / custom
            $table->string('label')->nullable(); // pl. "Mobil", "Számlázási cím", "Adószám" — üres = még nincs elnevezve
            $table->text('value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_fields');
    }
};
