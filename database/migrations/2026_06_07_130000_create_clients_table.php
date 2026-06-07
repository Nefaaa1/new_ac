<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table d'extension 1-1 du client (un client = un User type=client + cette fiche métier).
     * `id` = identité métier (référencée par les futurs contrats/sites) ; `user_id` = lien 1-1 unique.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('societe')->nullable();
            $table->string('lienapp')->nullable();
            $table->string('email3')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
