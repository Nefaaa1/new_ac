<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Champ libre « mois de paiement » (visible si paiement_agence). */
    public function up(): void
    {
        Schema::table('site_hebergement', function (Blueprint $table) {
            $table->string('paiement_mois')->nullable()->after('paiement_agence');
        });
    }

    public function down(): void
    {
        Schema::table('site_hebergement', function (Blueprint $table) {
            $table->dropColumn('paiement_mois');
        });
    }
};
