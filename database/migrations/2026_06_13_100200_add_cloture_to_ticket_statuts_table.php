<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Désigne LE statut terminal (clôture) → cible du futur bouton « Terminer ». */
    public function up(): void
    {
        Schema::table('ticket_statuts', function (Blueprint $table) {
            $table->boolean('cloture')->default(false)->after('position');
        });

        // Le statut « Terminée » seedé devient le statut de clôture.
        DB::table('ticket_statuts')->where('libelle', 'Terminée')->update(['cloture' => true]);
    }

    public function down(): void
    {
        Schema::table('ticket_statuts', function (Blueprint $table) {
            $table->dropColumn('cloture');
        });
    }
};
