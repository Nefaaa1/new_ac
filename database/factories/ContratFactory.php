<?php

namespace Database\Factories;

use App\Models\Contrat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contrat>
 */
class ContratFactory extends Factory
{
    protected $model = Contrat::class;

    public function definition(): array
    {
        return [
            'libelle' => fake()->words(3, true),
            'client_id' => null,
            'site_web' => fake()->optional()->url(),
            'type' => fake()->randomElement(array_keys(Contrat::TYPES)),
            'date_debut' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'date_fin' => null,
            'taux_horaire' => fake()->randomFloat(2, 30, 120),
            'cycle_facturation' => fake()->randomElement(array_keys(Contrat::CYCLES)),
            'credits' => fake()->numberBetween(1, 40),
        ];
    }
}
