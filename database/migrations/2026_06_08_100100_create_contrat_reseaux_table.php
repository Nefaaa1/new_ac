<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrat_reseaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained('contrats')->cascadeOnDelete();
            // facebook | instagram | x | linkedin | brevo | tiktok | pinterest | youtube
            $table->string('reseau');
            $table->string('identifiant')->nullable();
            // Chiffré au repos (cast 'encrypted') → colonne texte.
            $table->text('mot_de_passe')->nullable();
            // client | agence (facultatif)
            $table->string('gestion')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrat_reseaux');
    }
};
