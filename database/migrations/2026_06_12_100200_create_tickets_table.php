<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('demande');                 // titre de la demande
            $table->text('descriptif')->nullable();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->date('date')->nullable();
            $table->foreignId('statut_id')->nullable()->constrained('ticket_statuts')->nullOnDelete();
            // Devis (facultatif) : a_deviser + état du devis.
            $table->boolean('a_deviser')->default(false);
            $table->foreignId('devis_statut_id')->nullable()->constrained('devis_statuts')->nullOnDelete();
            $table->decimal('temps_intervention', 6, 2)->nullable(); // heures (ex. 1.50)
            // faible | moyenne | elevee
            $table->string('importance')->default('moyenne');
            // Attribué à / créateur (admins).
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('createur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
