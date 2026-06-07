<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Extension 1-1 du site : accès FTP (mot de passe chiffré au repos). */
    public function up(): void
    {
        Schema::create('site_ftp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->unique()->constrained('sites')->cascadeOnDelete();
            $table->string('hote')->nullable();
            $table->string('identifiant')->nullable();
            $table->text('mot_de_passe')->nullable();
            // Le client peut voir ces identifiants (espace client futur).
            $table->boolean('client_visible')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_ftp');
    }
};
