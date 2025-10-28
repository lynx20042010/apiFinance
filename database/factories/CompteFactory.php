<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'client_id' => \App\Models\Client::factory(),
            'numeroCompte' => \App\Models\Compte::generateNumeroCompte(),
            'type' => $this->faker->randomElement(['courant', 'epargne', 'titre', 'devise']),
            'devise' => $this->faker->randomElement(['XAF', 'EUR', 'USD', 'CAD']),
            'statut' => $this->faker->randomElement(['actif', 'inactif', 'bloque', 'ferme']),
            'solde' => $this->faker->randomFloat(2, 0, 1000000),
            'metadata' => [
                'date_ouverture' => $this->faker->date(),
                'agence' => $this->faker->city(),
                'rib' => $this->faker->iban('CM'),
                'iban' => $this->faker->iban()
            ]
        ];
    }

    /**
     * Compte courant
     */
    public function courant(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'courant',
            'solde' => $this->faker->randomFloat(2, -5000, 500000),
        ]);
    }

    /**
     * Compte épargne
     */
    public function epargne(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'epargne',
            'solde' => $this->faker->randomFloat(2, 0, 1000000),
        ]);
    }

    /**
     * Compte actif
     */
    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'actif',
        ]);
    }
}
