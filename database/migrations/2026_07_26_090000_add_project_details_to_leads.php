<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rob kérése (2026-07-26): a leadeknél legyen konkrét projekt-megnevezés, "mennyire
 * érzed nyerhetőnek %" (a korábbi általános "score" mező pontosítása erre a célra),
 * jelenlegi állás szöveggel, és egy mindig kitölthető (de nem kötelező) "következő
 * lépés" mező — ezek jelenjenek meg az összesítő kártyán is (lásd leads/index.blade.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->renameColumn('score', 'win_probability');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->string('project_title')->nullable()->after('company');
            $table->text('current_status_note')->nullable()->after('status');
            $table->text('next_step')->nullable()->after('current_status_note');
            $table->date('next_step_due_at')->nullable()->after('next_step');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['project_title', 'current_status_note', 'next_step', 'next_step_due_at']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->renameColumn('win_probability', 'score');
        });
    }
};
