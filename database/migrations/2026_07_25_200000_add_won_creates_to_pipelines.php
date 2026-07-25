<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Melyiket hozza létre a "won" hook: egyszeri projektet vagy ismétlődő retainert —
        // lásd architektura.md 5. pont ("projekt vagy retainer?" döntés, crm_projekt.md 7. szekció).
        Schema::table('pipelines', function (Blueprint $table) {
            $table->string('won_creates', 20)->default('project')->after('is_default'); // project / retainer / none
        });
    }

    public function down(): void
    {
        Schema::table('pipelines', function (Blueprint $table) {
            $table->dropColumn('won_creates');
        });
    }
};
