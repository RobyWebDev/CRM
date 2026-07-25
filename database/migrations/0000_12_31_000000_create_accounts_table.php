<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A tenant tábla — mindenki más ehhez kötődik account_id-n keresztül.
        // owner_user_id FK-ját külön migráció adja hozzá, miután a users tábla is létezik (körkörös hivatkozás).
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->string('subscription_tier')->default('free');
            $table->string('locale', 10)->default('hu');
            $table->string('timezone', 64)->default('Europe/Budapest');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
