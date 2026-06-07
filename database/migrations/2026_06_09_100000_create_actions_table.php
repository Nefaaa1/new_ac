<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->string('intitule');
            // Temps passé en heures (décimal, ex. 1.50 = 1h30).
            $table->decimal('temps', 6, 2);
            $table->date('date');
            // site_web | reseaux_sociaux | redaction | graphisme | intranet
            $table->string('type');
            $table->foreignId('contrat_id')->constrained('contrats')->cascadeOnDelete();
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
