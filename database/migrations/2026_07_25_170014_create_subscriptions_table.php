<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jövőbeli SaaS-fázis: mit fizet az adott account a CRM-ért. Előkészítve, MVP-ben nem használt.
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('tier', 50);
            $table->string('status', 20)->default('active'); // active / canceled / past_due
            $table->timestamp('started_at')->nullable();
            $table->timestamp('renewed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->string('external_ref')->nullable(); // Stripe/Barion azonosító
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
