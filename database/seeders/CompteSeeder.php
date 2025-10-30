<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des comptes pour les clients existants
        $clients = \App\Models\Client::all();

        foreach ($clients as $client) {
            // Chaque client a entre 1 et 3 comptes
            $nombreComptes = rand(1, 3);

            for ($i = 0; $i < $nombreComptes; $i++) {
                \App\Models\Compte::factory()->create([
                    'client_id' => $client->id,
                ]);
            }
        }

        // Créer des comptes spécifiques pour les tests
        $clientDupont = \App\Models\Client::where('numeroCompte', 'CLT2024000001')->first();
        if ($clientDupont) {
            // Compte courant
            \App\Models\Compte::factory()->create([
                'client_id' => $clientDupont->id,
                'numeroCompte' => 'CPT2024000001',
                'type' => 'courant',
                'devise' => 'XAF',
                'statut' => 'actif',
                'solde' => 150000.00,
                'metadata' => [
                    'date_ouverture' => '2024-01-15',
                    'agence' => 'Yaoundé Centre',
                    'rib' => 'CM2110123456789012345678901',
                    'iban' => 'CM2110123456789012345678901'
                ]
            ]);

            // Compte épargne (pour les tests de blocage)
            \App\Models\Compte::factory()->create([
                'client_id' => $clientDupont->id,
                'numeroCompte' => 'CPT2024000002',
                'type' => 'epargne',
                'devise' => 'EUR',
                'statut' => 'actif',
                'solde' => 25000.00,
                'metadata' => [
                    'date_ouverture' => '2024-02-20',
                    'agence' => 'Yaoundé Centre',
                    'rib' => 'CM2110123456789012345678902',
                    'iban' => 'CM2110123456789012345678902'
                ]
            ]);

            // Compte chèque
            \App\Models\Compte::factory()->create([
                'client_id' => $clientDupont->id,
                'numeroCompte' => 'CPT2024000004',
                'type' => 'cheque',
                'devise' => 'XAF',
                'statut' => 'actif',
                'solde' => 75000.00,
                'metadata' => [
                    'date_ouverture' => '2024-04-05',
                    'agence' => 'Yaoundé Centre',
                    'rib' => 'CM2110123456789012345678904',
                    'iban' => 'CM2110123456789012345678904'
                ]
            ]);
        }

        $clientTechCorp = \App\Models\Client::where('numeroCompte', 'CLT2024000002')->first();
        if ($clientTechCorp) {
            // Compte courant
            \App\Models\Compte::factory()->create([
                'client_id' => $clientTechCorp->id,
                'numeroCompte' => 'CPT2024000003',
                'type' => 'courant',
                'devise' => 'EUR',
                'statut' => 'actif',
                'solde' => 500000.00,
                'metadata' => [
                    'date_ouverture' => '2024-03-10',
                    'agence' => 'Douala Bonanjo',
                    'rib' => 'CM2110123456789012345678903',
                    'iban' => 'CM2110123456789012345678903'
                ]
            ]);

            // Compte épargne pour TechCorp
            \App\Models\Compte::factory()->create([
                'client_id' => $clientTechCorp->id,
                'numeroCompte' => 'CPT2024000005',
                'type' => 'epargne',
                'devise' => 'USD',
                'statut' => 'actif',
                'solde' => 100000.00,
                'metadata' => [
                    'date_ouverture' => '2024-05-12',
                    'agence' => 'Douala Bonanjo',
                    'rib' => 'CM2110123456789012345678905',
                    'iban' => 'CM2110123456789012345678905'
                ]
            ]);

            // Compte chèque pour TechCorp
            \App\Models\Compte::factory()->create([
                'client_id' => $clientTechCorp->id,
                'numeroCompte' => 'CPT2024000006',
                'type' => 'cheque',
                'devise' => 'XAF',
                'statut' => 'actif',
                'solde' => 200000.00,
                'metadata' => [
                    'date_ouverture' => '2024-06-18',
                    'agence' => 'Douala Bonanjo',
                    'rib' => 'CM2110123456789012345678906',
                    'iban' => 'CM2110123456789012345678906'
                ]
            ]);
        }
    }
}
