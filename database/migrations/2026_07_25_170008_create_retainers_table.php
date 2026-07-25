<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ismétlődő (havi/negyedéves) megbízások — külön a projects (egyszeri) rekordoktól.
        // Döntés: 2026-07-25, lásd crm_projekt.md 7. szekció.
        Schema::create('retainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('monthly_fee', 12, 2)->nullable();
            $table->string('billing_cycle', 20)->default('monthly'); // monthly / quarterly / other
            $table->unsignedTinyInteger('billing_day')->nullable();
            $table->string('status', 20)->default('active'); // active / paused / ended
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retainers');
    }
};
