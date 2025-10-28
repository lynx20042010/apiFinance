<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['depot', 'retrait', 'virement', 'transfert', 'commission', 'interet']);
        $compte = \App\Models\Compte::factory()->create();

        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'compte_id' => $compte->id,
            'type' => $type,
            'montant' => $this->faker->randomFloat(2, 100, 100000),
            'devise' => $compte->devise,
            'description' => $this->faker->sentence(),
            'compte_destination_id' => in_array($type, ['virement', 'transfert'])
                ? \App\Models\Compte::factory()->create()->id
                : null,
            'statut' => $this->faker->randomElement(['en_attente', 'traitee', 'annulee', 'echouee']),
            'date_execution' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'metadata' => [
                'canal' => $this->faker->randomElement(['guichet', 'mobile', 'web', 'api']),
                'reference_externe' => $this->faker->optional(0.3)->uuid(),
                'frais' => $this->faker->optional(0.2)->randomFloat(2, 0, 5000),
                'motif' => $this->faker->optional(0.5)->sentence()
            ]
        ];
    }

    /**
     * Transaction de dépôt
     */
    public function depot(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'depot',
            'compte_destination_id' => null,
            'statut' => 'traitee',
        ]);
    }

    /**
     * Transaction de retrait
     */
    public function retrait(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'retrait',
            'compte_destination_id' => null,
            'statut' => 'traitee',
        ]);
    }

    /**
     * Transaction de virement
     */
    public function virement(): static
    {
        return $this->state(function (array $attributes) {
            $compteDestination = \App\Models\Compte::factory()->create();
            return [
                'type' => 'virement',
                'compte_destination_id' => $compteDestination->id,
                'statut' => 'traitee',
            ];
        });
    }

    /**
     * Transaction traitée
     */
    public function traitee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'traitee',
            'date_execution' => now(),
        ]);
    }

    /**
     * Transaction en attente
     */
    public function enAttente(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'en_attente',
            'date_execution' => null,
        ]);
    }
}
