<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Accès accordés à un admin restreint, vers une ressource précise
     * (polymorphe : un Client, un Site, un Contrat…). Granulaire par ressource.
     */
    public function up(): void
    {
        Schema::create('access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('grantable'); // grantable_type + grantable_id
            $table->timestamps();

            $table->unique(['user_id', 'grantable_type', 'grantable_id'], 'access_grants_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_grants');
    }
};
