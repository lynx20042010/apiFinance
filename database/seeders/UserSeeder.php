<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR');

        // Créer un administrateur
        $adminUser = User::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Administrateur Système',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        Admin::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $adminUser->id,
            'role' => 'super_admin',
            'metadata' => json_encode([
                'created_by' => 'system',
                'department' => 'IT',
                'level' => 'super_admin',
                'permissions' => [
                    'comptes.create',
                    'comptes.read',
                    'comptes.update',
                    'comptes.delete',
                    'comptes.block',
                    'comptes.archive',
                    'comptes.unarchive',
                    'users.manage',
                    'system.admin'
                ]
            ])
        ]);

        // Créer 10 clients avec des données Faker
        for ($i = 1; $i <= 10; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $fullName = $firstName . ' ' . $lastName;

            $clientUser = User::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'name' => $fullName,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('client123'),
                'email_verified_at' => now(),
            ]);

            $numeroCompte = 'CLT' . date('Y') . str_pad($i, 6, '0', STR_PAD_LEFT);

            $client = Client::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $clientUser->id,
                'numeroCompte' => $numeroCompte,
                'titulaire' => $fullName,
                'type' => 'particulier',
                'devise' => 'XAF',
                'statut' => 'actif',
                'metadata' => json_encode([
                    'telephone' => $faker->phoneNumber,
                    'adresse' => $faker->address,
                    'code_authentification' => str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'date_naissance' => $faker->date('Y-m-d', '-18 years'),
                    'profession' => $faker->jobTitle
                ])
            ]);

            // Créer 1-3 comptes par client
            $numComptes = rand(1, 3);
            for ($j = 1; $j <= $numComptes; $j++) {
                $types = ['courant', 'epargne', 'cheque'];
                $devises = ['XAF', 'EUR', 'USD'];
                $type = $faker->randomElement($types);
                $devise = $faker->randomElement($devises);
                $solde = $faker->randomFloat(2, 1000, 500000);

                $numeroCompteCPT = 'CPT' . date('Y') . str_pad(($i * 10) + $j, 6, '0', STR_PAD_LEFT);

                \App\Models\Compte::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'client_id' => $client->id,
                    'numeroCompte' => $numeroCompteCPT,
                    'type' => $type,
                    'devise' => $devise,
                    'statut' => 'actif',
                    'solde' => $solde,
                    'metadata' => json_encode([
                        'date_ouverture' => $faker->date('Y-m-d', '-1 year'),
                        'agence' => $faker->randomElement(['Dakar Centre', 'Saint-Louis Centre', 'Thiès Centre', 'Ziguinchor Centre']),
                        'rib' => 'SN' . str_pad(mt_rand(100000000000000000000000, 999999999999999999999999), 24, '0', STR_PAD_LEFT),
                        'iban' => 'SN' . str_pad(mt_rand(100000000000000000000000, 999999999999999999999999), 24, '0', STR_PAD_LEFT)
                    ])
                ]);
            }
        }

        // Créer quelques comptes épargne bloqués pour les tests (sur Neon)
        $blockedClients = Client::take(3)->get();
        foreach ($blockedClients as $index => $client) {
            $compteBloque = new \App\Models\Compte();
            $compteBloque->id = (string) \Illuminate\Support\Str::uuid();
            $compteBloque->client_id = $client->id;
            $compteBloque->numeroCompte = 'CPT' . date('Y') . 'BLK' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $compteBloque->type = 'epargne';
            $compteBloque->devise = 'XAF';
            $compteBloque->statut = 'bloque';
            $compteBloque->solde = $faker->randomFloat(2, 50000, 200000);
            $compteBloque->metadata = json_encode([
                'date_ouverture' => $faker->date('Y-m-d', '-6 months'),
                'agence' => 'Dakar Centre',
                'rib' => 'SN' . str_pad(mt_rand(100000000000000000000000, 999999999999999999999999), 24, '0', STR_PAD_LEFT),
                'iban' => 'SN' . str_pad(mt_rand(100000000000000000000000, 999999999999999999999999), 24, '0', STR_PAD_LEFT),
                'dateBlocage' => now()->subDays(rand(10, 45))->toISOString(),
                'dateFinBlocage' => now()->addDays(rand(1, 30))->toISOString(),
                'motifBlocage' => $faker->randomElement(['Épargne à terme', 'Blocage judiciaire', 'Demande client'])
            ]);
            $compteBloque->setConnection('neon');
            $compteBloque->save();
        }

        $this->command->info('Utilisateurs créés avec succès:');
        $this->command->info('Admin: admin@example.com / admin123');
        $this->command->info('10 clients créés avec des données Faker');
        $this->command->info('Mot de passe client par défaut: client123');
        $this->command->info('3 comptes épargne bloqués créés pour les tests');
    }
}
