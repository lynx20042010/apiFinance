<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un admin par défaut pour les tests
        $user = \App\Models\User::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        \App\Models\Admin::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'role' => 'admin',
            'permissions' => json_encode([
                'comptes.create',
                'comptes.read',
                'comptes.update',
                'comptes.delete',
                'comptes.block',
                'comptes.archive',
                'comptes.unarchive',
                'users.manage',
                'system.admin'
            ]),
            'metadata' => json_encode([
                'created_by' => 'seeder',
                'department' => 'IT',
                'level' => 'admin'
            ])
        ]);
    }
}
