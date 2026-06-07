<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            // Client facultatif : un contrat peut exister sans client rattaché.
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('site_web')->nullable();
            // horaire | horaire_sup | fixe | sup_temps_reel
            $table->string('type')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->decimal('taux_horaire', 10, 2)->nullable();
            // mensuel | trimestriel | annuel
            $table->string('cycle_facturation')->nullable();
            // 1 crédit = 1h
            $table->unsignedInteger('credits')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
