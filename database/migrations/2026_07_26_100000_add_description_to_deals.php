<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rob kérése (2026-07-26): a leadnél felvett "projekt scope" kérdésre válaszul —
 * a Deal-nek eddig nem volt leírás-mezője (csak title), pedig a Project-nek már
 * van. Így a scope természetesen mélyülhet: Lead (project_title, röviden) →
 * Deal (description, mit ajánlunk/tárgyalunk) → Project (description, mit
 * valósítunk meg ténylegesen).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
