<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Extension 1-1 du site : WordPress (mots de passe chiffrés au repos). */
    public function up(): void
    {
        Schema::create('site_wordpress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->unique()->constrained('sites')->cascadeOnDelete();
            $table->string('lien_admin')->nullable();
            $table->string('identifiant_admin')->nullable();
            $table->text('mot_de_passe_admin')->nullable();
            $table->string('lien_client')->nullable();
            $table->string('identifiant_client')->nullable();
            $table->text('mot_de_passe_client')->nullable();
            // Le client peut voir ces identifiants (espace client futur).
            $table->boolean('client_visible')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_wordpress');
    }
};
