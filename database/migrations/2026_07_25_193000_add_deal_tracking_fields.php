<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Mikor került a deal a JELENLEGI lépésére — az updated_at bármilyen mezőváltozásnál
            // frissül, ezért külön mező kell a "hány napja áll ebben a lépésben" (elakadt
            // deal) jelzéshez, CRM best practice (pl. HubSpot "deal stalled" jelzése).
            $table->timestamp('stage_entered_at')->nullable()->after('closed_at');

            // Miért veszett el az üzlet — CRM best practice (Salesforce/HubSpot "lost reason"),
            // hogy tanulni lehessen a bukott üzletekből.
            $table->text('lost_reason')->nullable()->after('invoice_status');
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn(['stage_entered_at', 'lost_reason']);
        });
    }
};
