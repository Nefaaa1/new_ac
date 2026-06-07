<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Extension 1-1 du site : hébergement (mot de passe chiffré au repos). */
    public function up(): void
    {
        Schema::create('site_hebergement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->unique()->constrained('sites')->cascadeOnDelete();
            $table->string('nom')->nullable();
            $table->string('registrar')->nullable();
            $table->string('identifiant')->nullable();
            $table->text('mot_de_passe')->nullable();
            // mensuelle | annuelle
            $table->string('periode_renouvellement')->nullable();
            $table->boolean('paiement_agence')->default(false);
            // Le client peut voir ces identifiants (espace client futur).
            $table->boolean('client_visible')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_hebergement');
    }
};
