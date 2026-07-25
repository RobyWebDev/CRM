<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Címkék (tags) — MiniCRM-inspiráció (docs/minicrm-inspiracio.md 6. pont): szabadon
        // felvehető, kontaktokhoz/szervezetekhez rendelhető, szűrésben is használható jelölők.
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 20)->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'name']);
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->morphs('taggable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
    }
};
