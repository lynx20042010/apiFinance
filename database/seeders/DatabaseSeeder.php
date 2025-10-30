<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer des utilisateurs de test
        \App\Models\User::factory(5)->create();

        // Créer un utilisateur admin
        $adminUser = \App\Models\User::factory()->create([
            'name' => 'Admin System',
            'email' => 'admin@apifinance.com',
            'email_verified_at' => now(),
        ]);

        // Créer l'admin associé
        \App\Models\Admin::factory()->create([
            'user_id' => $adminUser->id,
            'role' => 'super_admin',
        ]);

        // Créer un utilisateur client
        $clientUser = \App\Models\User::factory()->create([
            'name' => 'Client Test',
            'email' => 'client@apifinance.com',
            'email_verified_at' => now(),
        ]);

        // Créer le client associé
        \App\Models\Client::factory()->create([
            'user_id' => $clientUser->id,
            'numeroCompte' => 'CLT2024000000',
            'titulaire' => 'Client Test',
            'type' => 'particulier',
            'statut' => 'actif',
        ]);

        // Lancer les seeders spécifiques
        $this->call([
            UserSeeder::class,
            ClientSeeder::class,
            CompteSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
