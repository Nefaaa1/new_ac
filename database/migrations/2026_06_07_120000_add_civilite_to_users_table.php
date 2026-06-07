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
            $table->enum('civilite', ['M', 'Mme'])->nullable()->after('prenom');
        });

        // Le super-admin existant : civilité par défaut.
        DB::table('users')->where('login', 'antoinepw')->update(['civilite' => 'M']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('civilite');
        });
    }
};
