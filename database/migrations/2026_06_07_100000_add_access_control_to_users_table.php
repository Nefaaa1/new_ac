<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Niveau d'accès d'un admin : 'full' = tout, 'restricted' = limité aux grants.
            $table->enum('access_level', ['full', 'restricted'])->default('restricted')->after('type');
            // Suspension : compte désactivé sans être supprimé (réactivable).
            $table->timestamp('suspended_at')->nullable()->after('access_level');
        });

        // Les admins déjà en base conservent un accès total.
        DB::table('users')->where('type', 'admin')->update(['access_level' => 'full']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['access_level', 'suspended_at']);
        });
    }
};
