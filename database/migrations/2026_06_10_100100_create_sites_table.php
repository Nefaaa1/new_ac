<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            // Client facultatif : un site peut exister sans client rattaché.
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->boolean('boutique_en_ligne')->default(false);
            $table->foreignId('statut_id')->nullable()->constrained('statuts')->nullOnDelete();
            // Obligatoire seulement si le statut choisi a requiert_date = true.
            $table->date('date_statut')->nullable();
            // Champ texte libre (chiffré au repos, cast 'encrypted').
            $table->text('mot_de_passe_complementaire')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
