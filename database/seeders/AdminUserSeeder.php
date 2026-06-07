<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Crée le compte admin principal (antoinepw).
     *
     * Idempotent : ne fait rien si le login existe déjà.
     * À lancer une fois sur le serveur : php artisan db:seed --class=AdminUserSeeder
     */
    public function run(): void
    {
        if (User::where('login', User::SUPER_ADMIN_LOGIN)->exists()) {
            $this->command->warn("L'utilisateur '".User::SUPER_ADMIN_LOGIN."' existe déjà — aucune action.");

            return;
        }

        $password = Str::password(16);

        User::create([
            'type' => 'admin',
            'access_level' => 'full',
            'login' => User::SUPER_ADMIN_LOGIN,
            'password' => Hash::make($password),
            'nom' => 'Fagnere',
            'prenom' => 'Antoine',
            'email' => 'antoine.fagnere@gmail.com',
        ]);

        $this->command->newLine();
        $this->command->info('Compte admin créé : '.User::SUPER_ADMIN_LOGIN);
        $this->command->warn("Mot de passe (à noter, affiché une seule fois) : {$password}");
        $this->command->newLine();
    }
}
