<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Attribution à une équipe (en plus de utilisateur_id) + traçabilité de clôture.
     * Règle métier : exactement un de utilisateur_id / equipe_id (validée côté form).
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('equipe_id')->nullable()->after('utilisateur_id')->constrained('equipes')->nullOnDelete();
            $table->timestamp('terminee_at')->nullable()->after('createur_id');
            $table->foreignId('termine_par_id')->nullable()->after('terminee_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('equipe_id');
            $table->dropConstrainedForeignId('termine_par_id');
            $table->dropColumn('terminee_at');
        });
    }
};
