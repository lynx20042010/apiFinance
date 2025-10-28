<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer 10 clients de test
        \App\Models\Client::factory(10)->create();

        // Créer des clients spécifiques pour les tests
        \App\Models\Client::factory()->create([
            'numeroCompte' => 'CLT2024000001',
            'titulaire' => 'Jean Dupont',
            'type' => 'particulier',
            'devise' => 'XAF',
            'statut' => 'actif',
            'metadata' => [
                'telephone' => '+237 677 123 456',
                'adresse' => '123 Rue de la Paix, Yaoundé',
                'date_naissance' => '1985-05-15',
                'profession' => 'Ingénieur'
            ]
        ]);

        \App\Models\Client::factory()->create([
            'numeroCompte' => 'CLT2024000002',
            'titulaire' => 'Marie Tech Corp',
            'type' => 'entreprise',
            'devise' => 'EUR',
            'statut' => 'actif',
            'metadata' => [
                'telephone' => '+237 699 987 654',
                'adresse' => '456 Avenue des Affaires, Douala',
                'secteur' => 'Technologie',
                'numero_rc' => 'RC/DLA/2020/B/1234'
            ]
        ]);
    }
}
