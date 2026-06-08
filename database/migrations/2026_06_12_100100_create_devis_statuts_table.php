<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** États de devis configurables (rubrique Gestion) : à deviser / validé / … */
    public function up(): void
    {
        Schema::create('devis_statuts', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('couleur')->nullable(); // hex #RRGGBB
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $now = now();
        DB::table('devis_statuts')->insert([
            ['libelle' => 'À deviser',              'couleur' => '#71717a', 'position' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['libelle' => 'En attente de validation', 'couleur' => '#F6A900', 'position' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['libelle' => 'Validé',                 'couleur' => '#10b981', 'position' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['libelle' => 'Refusé',                 'couleur' => '#ef4444', 'position' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('devis_statuts');
    }
};
