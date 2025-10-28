<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
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
            'user_id' => \App\Models\User::factory()->create()->id,
            'numeroCompte' => \App\Models\Client::generateNumeroCompte(),
            'titulaire' => $this->faker->name(),
            'type' => $this->faker->randomElement(['particulier', 'entreprise']),
            'devise' => $this->faker->randomElement(['XAF', 'EUR', 'USD']),
            'statut' => $this->faker->randomElement(['actif', 'inactif', 'suspendu']),
            'metadata' => [
                'telephone' => $this->faker->phoneNumber(),
                'adresse' => $this->faker->address(),
                'date_naissance' => $this->faker->date(),
                'profession' => $this->faker->jobTitle()
            ]
        ];
    }
}
