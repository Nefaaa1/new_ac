<?php

namespace Database\Seeders;

use App\Models\Action;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Données de démonstration : clients + contrats + actions étalées sur l'année,
 * pour alimenter les listes et le récap mensuel.
 *
 * Lancement : php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();

        // 1) Clients (User type=client + fiche métier) ----------------------
        $clients = collect();

        for ($i = 0; $i < 10; $i++) {
            $prenom = $faker->firstName();
            $nom = $faker->lastName();
            $login = Str::lower(preg_replace('/[^a-z0-9]/', '', Str::ascii($prenom.$nom))).$i;

            $user = User::factory()->create([
                'type' => 'client',
                'login' => $login,
                'prenom' => $prenom,
                'nom' => $nom,
            ]);

            Client::create([
                'user_id' => $user->id,
                'societe' => $faker->company(),
                'lienapp' => $faker->optional()->url(),
            ]);

            $clients->push($user->client);
        }

        // 2) Contrats (variés en type + cycle, certains sans client) ---------
        $contrats = collect();

        for ($i = 0; $i < 28; $i++) {
            $contrats->push(Contrat::factory()->create([
                'libelle' => $faker->company(),
                'client_id' => $faker->boolean(80) ? $clients->random()->id : null,
                'type' => $faker->randomElement(array_keys(Contrat::TYPES)),
                'cycle_facturation' => $faker->randomElement(array_keys(Contrat::CYCLES)),
                'taux_horaire' => $faker->randomFloat(2, 35, 110),
                'credits' => $faker->numberBetween(2, 35),
                'date_debut' => $faker->dateTimeBetween('-2 years', '-2 months')->format('Y-m-d'),
            ]));
        }

        // 3) Actions étalées sur les mois (année courante + fin d'an dernier)
        $now = Carbon::now();
        $types = array_keys(Action::TYPES);
        $total = 0;

        foreach ($contrats as $contrat) {
            // Quelques contrats sans aucune action (apparaissent quand même via le crédit).
            if ($faker->boolean(12)) {
                continue;
            }

            // Plage de mois : de janvier (an dernier en partie) jusqu'au mois courant.
            $start = $now->copy()->subMonths($faker->numberBetween(6, 14))->startOfMonth();

            $cursor = $start->copy();
            while ($cursor->lte($now)) {
                // 0 à 6 actions par mois pour ce contrat (volume variable → statuts variés).
                $nb = $faker->numberBetween(0, 6);

                for ($a = 0; $a < $nb; $a++) {
                    Action::factory()->create([
                        'contrat_id' => $contrat->id,
                        'type' => $faker->randomElement($types),
                        'temps' => $faker->randomFloat(2, 0.25, 6),
                        'date' => $faker->dateTimeBetween(
                            $cursor->copy()->startOfMonth(),
                            min($cursor->copy()->endOfMonth(), $now)
                        )->format('Y-m-d'),
                    ]);
                    $total++;
                }

                $cursor->addMonth();
            }
        }

        $this->command->info("Démo générée : {$clients->count()} clients, {$contrats->count()} contrats, {$total} actions.");
    }
}
