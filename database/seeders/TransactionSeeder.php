<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des transactions pour les comptes existants
        $comptes = \App\Models\Compte::all();

        foreach ($comptes as $compte) {
            // Chaque compte a entre 5 et 15 transactions
            $nombreTransactions = rand(5, 15);

            for ($i = 0; $i < $nombreTransactions; $i++) {
                \App\Models\Transaction::factory()->create([
                    'compte_id' => $compte->id,
                ]);
            }
        }

        // Créer des transactions spécifiques pour les comptes de test
        $compteDupontCourant = \App\Models\Compte::where('numeroCompte', 'CPT2024000001')->first();
        if ($compteDupontCourant) {
            // Dépôts
            \App\Models\Transaction::factory()->create([
                'compte_id' => $compteDupontCourant->id,
                'type' => 'depot',
                'montant' => 50000.00,
                'description' => 'Dépôt salaire',
                'statut' => 'traitee',
                'date_execution' => now()->subDays(30),
                'metadata' => ['canal' => 'guichet', 'motif' => 'Salaire mensuel']
            ]);

            \App\Models\Transaction::factory()->create([
                'compte_id' => $compteDupontCourant->id,
                'type' => 'depot',
                'montant' => 25000.00,
                'description' => 'Virement familial',
                'statut' => 'traitee',
                'date_execution' => now()->subDays(15),
                'metadata' => ['canal' => 'mobile', 'motif' => 'Soutien familial']
            ]);

            // Retraits
            \App\Models\Transaction::factory()->create([
                'compte_id' => $compteDupontCourant->id,
                'type' => 'retrait',
                'montant' => 15000.00,
                'description' => 'Retrait DAB',
                'statut' => 'traitee',
                'date_execution' => now()->subDays(10),
                'metadata' => ['canal' => 'dab', 'motif' => 'Courses']
            ]);

            // Virement vers compte épargne
            $compteDupontEpargne = \App\Models\Compte::where('numeroCompte', 'CPT2024000002')->first();
            if ($compteDupontEpargne) {
                \App\Models\Transaction::factory()->create([
                    'compte_id' => $compteDupontCourant->id,
                    'type' => 'virement',
                    'montant' => 20000.00,
                    'description' => 'Virement vers épargne',
                    'compte_destination_id' => $compteDupontEpargne->id,
                    'statut' => 'traitee',
                    'date_execution' => now()->subDays(5),
                    'metadata' => ['canal' => 'web', 'motif' => 'Épargne']
                ]);
            }
        }

        $compteTechCorp = \App\Models\Compte::where('numeroCompte', 'CPT2024000003')->first();
        if ($compteTechCorp) {
            // Transactions d'entreprise
            \App\Models\Transaction::factory()->create([
                'compte_id' => $compteTechCorp->id,
                'type' => 'depot',
                'montant' => 200000.00,
                'description' => 'Paiement client - Projet développement',
                'statut' => 'traitee',
                'date_execution' => now()->subDays(20),
                'metadata' => ['canal' => 'virement', 'motif' => 'Paiement projet']
            ]);

            \App\Models\Transaction::factory()->create([
                'compte_id' => $compteTechCorp->id,
                'type' => 'retrait',
                'montant' => 75000.00,
                'description' => 'Paiement fournisseurs',
                'statut' => 'traitee',
                'date_execution' => now()->subDays(12),
                'metadata' => ['canal' => 'virement', 'motif' => 'Fournitures informatiques']
            ]);

            // Intérêts
            \App\Models\Transaction::factory()->create([
                'compte_id' => $compteTechCorp->id,
                'type' => 'interet',
                'montant' => 1250.00,
                'description' => 'Intérêts créditeurs',
                'statut' => 'traitee',
                'date_execution' => now()->subDays(1),
                'metadata' => ['canal' => 'automatique', 'motif' => 'Intérêts mensuels']
            ]);
        }
    }
}
