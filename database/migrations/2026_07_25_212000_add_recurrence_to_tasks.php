<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ismétlődő teendők — MiniCRM-inspiráció (docs/minicrm-inspiracio.md 5. pont):
        // pl. "minden hónap 5-én emlékeztető a számlaküldésre". Amikor egy ilyen teendőt
        // készre jelölnek, a rendszer automatikusan létrehozza a következő előfordulást.
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('recurrence', 20)->nullable()->after('status'); // null / daily / weekly / monthly
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('recurrence');
        });
    }
};
