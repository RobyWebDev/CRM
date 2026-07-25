<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ez a tábla teszi lehetővé, hogy FEJLESZTŐ NÉLKÜL bármilyen egyedi mezőt fel lehessen
        // venni bármelyik entitáshoz, akár szolgáltatás-típusonként eltérőt. Lásd adatmodell.md.
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('entity_type', 50); // contact / organization / deal / project / retainer
            $table->string('field_key', 100);
            $table->string('label');
            $table->string('field_type', 50); // text / textarea / number / date / boolean / select / multiselect / url
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['account_id', 'entity_type', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_definitions');
    }
};
