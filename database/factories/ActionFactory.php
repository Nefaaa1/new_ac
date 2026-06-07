<?php

namespace Database\Factories;

use App\Models\Action;
use App\Models\Contrat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Action>
 */
class ActionFactory extends Factory
{
    protected $model = Action::class;

    public function definition(): array
    {
        return [
            'intitule' => fake()->sentence(4),
            'temps' => fake()->randomFloat(2, 0.5, 8),
            'date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'type' => fake()->randomElement(array_keys(Action::TYPES)),
            'contrat_id' => Contrat::factory(),
            'commentaire' => fake()->optional()->sentence(),
        ];
    }
}
