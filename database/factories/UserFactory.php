<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'client',
            'login' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'civilite' => fake()->randomElement(['M', 'Mme']),
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'email_secondaire' => null,
            'telephone' => fake()->phoneNumber(),
        ];
    }

    /**
     * Administrateur avec accès total.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'admin',
            'access_level' => 'full',
        ]);
    }

    /**
     * Administrateur à accès restreint (limité à ses grants).
     */
    public function restricted(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'admin',
            'access_level' => 'restricted',
        ]);
    }

    /**
     * Compte suspendu.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'suspended_at' => now(),
        ]);
    }
}
