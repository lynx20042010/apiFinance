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

        // Créer un utilisateur admin avec mot de passe connu
        $adminUser = \App\Models\User::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Admin System',
            'email' => 'admin@apifinance.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        // Créer l'admin associé
        \App\Models\Admin::factory()->create([
            'user_id' => $adminUser->id,
            'role' => 'admin',
        ]);

        // Créer un utilisateur client avec mot de passe connu
        $clientUser = \App\Models\User::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Client Test',
            'email' => 'client@apifinance.com',
            'password' => \Illuminate\Support\Facades\Hash::make('client123'),
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
            AdminSeeder::class,
            UserSeeder::class,
            ClientSeeder::class,
            CompteSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
