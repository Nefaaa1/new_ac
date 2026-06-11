<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actions', function (Blueprint $table) {
            // Admin ayant saisi l'action (stats « mes actions du mois » sur le dashboard).
            // Nullable : les actions historiques n'ont pas de créateur connu.
            $table->foreignId('createur_id')->nullable()->after('commentaire')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('actions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('createur_id');
        });
    }
};
