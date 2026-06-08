<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Membres d'une équipe (admins). */
    public function up(): void
    {
        Schema::create('equipe_user', function (Blueprint $table) {
            $table->foreignId('equipe_id')->constrained('equipes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['equipe_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipe_user');
    }
};
