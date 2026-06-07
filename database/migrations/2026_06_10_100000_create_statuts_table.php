<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Statuts de site (rubrique Gestion). `requiert_date` = si vrai, un site
     * portant ce statut doit renseigner sa `date_statut`.
     */
    public function up(): void
    {
        Schema::create('statuts', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            // Couleur d'affichage (hex #RRGGBB).
            $table->string('couleur')->nullable();
            // Si vrai → date_statut obligatoire sur le site.
            $table->boolean('requiert_date')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuts');
    }
};
