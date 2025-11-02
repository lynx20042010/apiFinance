<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
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
            'user_id' => \App\Models\User::factory(),
            'role' => 'admin',
            'metadata' => [
                'departement' => $this->faker->randomElement(['IT', 'Finance', 'Support', 'Management']),
                'niveau_acces' => $this->faker->numberBetween(1, 5),
                'date_embauche' => $this->faker->date(),
                'superviseur' => $this->faker->name()
            ]
        ];
    }

    /**
     * Admin standard
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'niveau_acces' => 5,
                'privileges' => ['all']
            ])
        ]);
    }


}
