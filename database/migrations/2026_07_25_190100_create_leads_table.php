<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lead = még nem minősített érdeklődő — a klasszikus CRM best practice szerint (pl.
        // Salesforce Lead objektum) külön a Contacttól, amíg nem derül ki, hogy valódi,
        // munkára érdemes kapcsolat-e. "Konvertáláskor" lesz belőle Contact (+ opcionálisan Deal).
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('service_type_id')->nullable()->constrained()->nullOnDelete();

            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('company')->nullable();
            $table->string('source')->nullable(); // pl. weboldal, ajánlás, hideg hívás
            $table->string('status', 20)->default('new'); // new / contacted / qualified / unqualified / converted
            $table->unsignedTinyInteger('score')->nullable(); // egyszerű 0-100 lead-pontszám
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();

            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('converted_deal_id')->nullable()->constrained('deals')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
