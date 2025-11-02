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
        // Créer 15 comptes d'épargne actifs pour 3 clients propriétaires
        $clients = \App\Models\Client::all();

        // S'assurer qu'on a au moins 3 clients
        if ($clients->count() < 3) {
            // Créer des clients supplémentaires si nécessaire
            for ($i = $clients->count(); $i < 3; $i++) {
                $user = \App\Models\User::factory()->create([
                    'name' => 'Client Propriétaire ' . ($i + 1),
                    'email' => 'proprietaire' . ($i + 1) . '@apifinance.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('proprio123'),
                    'email_verified_at' => now(),
                ]);

                $client = \App\Models\Client::factory()->create([
                    'user_id' => $user->id,
                    'numeroCompte' => 'CLT' . date('Y') . str_pad(($i + 1), 6, '0', STR_PAD_LEFT),
                    'titulaire' => 'Client Propriétaire ' . ($i + 1),
                    'type' => 'particulier',
                    'statut' => 'actif',
                ]);

                $clients->push($client);
            }
        }

        // Créer 15 comptes d'épargne actifs répartis entre les 3 premiers clients
        $clientsProprietaires = $clients->take(3);
        $comptesParClient = 5; // 15 / 3 = 5 comptes par client

        foreach ($clientsProprietaires as $client) {
            for ($i = 0; $i < $comptesParClient; $i++) {
                \App\Models\Compte::factory()->epargne()->actif()->create([
                    'client_id' => $client->id,
                    'solde' => rand(10000, 500000), // Solde entre 10k et 500k
                ]);
            }
        }

        // Créer des comptes supplémentaires pour les autres clients existants
        $autresClients = $clients->skip(3);
        foreach ($autresClients as $client) {
            // Chaque client a entre 1 et 3 comptes
            $nombreComptes = rand(1, 3);

            for ($i = 0; $i < $nombreComptes; $i++) {
                \App\Models\Compte::factory()->actif()->create([
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

            // Compte épargne bloqué pour les tests
            \App\Models\Compte::factory()->create([
                'client_id' => $clientDupont->id,
                'numeroCompte' => 'CPT2024000007',
                'type' => 'epargne',
                'devise' => 'XAF',
                'statut' => 'bloque',
                'solde' => 50000.00,
                'metadata' => [
                    'date_ouverture' => '2024-07-01',
                    'agence' => 'Yaoundé Centre',
                    'rib' => 'CM2110123456789012345678907',
                    'iban' => 'CM2110123456789012345678907',
                    'motifBlocage' => 'Inactivité prolongée',
                    'dateBlocage' => '2024-10-15T10:00:00Z',
                    'dureeBlocage' => 30,
                    'dateFinBlocage' => '2024-11-14T10:00:00Z',
                    'statutAvantBlocage' => 'actif'
                ]
            ]);

            // Compte courant bloqué pour les tests
            \App\Models\Compte::factory()->create([
                'client_id' => $clientDupont->id,
                'numeroCompte' => 'CPT2024000008',
                'type' => 'courant',
                'devise' => 'EUR',
                'statut' => 'bloque',
                'solde' => 100000.00,
                'metadata' => [
                    'date_ouverture' => '2024-08-10',
                    'agence' => 'Yaoundé Centre',
                    'rib' => 'CM2110123456789012345678908',
                    'iban' => 'CM2110123456789012345678908',
                    'motifBlocage' => 'Suspicion d\'activité frauduleuse',
                    'dateBlocage' => '2024-10-20T14:30:00Z',
                    'dureeBlocage' => 60,
                    'dateFinBlocage' => '2024-12-19T14:30:00Z',
                    'statutAvantBlocage' => 'actif'
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
