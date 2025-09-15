<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar empresas para associar usuários
        $companies = Company::all();
        
        if ($companies->isEmpty()) {
            $this->command->error('❌ Nenhuma empresa encontrada. Execute CompanySeeder primeiro.');
            return;
        }

        $company1 = $companies->first();
        $company2 = $companies->count() > 1 ? $companies->skip(1)->first() : $company1;

        $users = [
            // Admin do sistema
            [
                'name' => 'Administrador do Sistema',
                'email' => 'admin@sistema.com',
                'password' => Hash::make('admin123'),
                'phone' => '(11) 99999-0000',
                'id_comp' => $company1->id,
                'tipo' => 'admin',
                'created_at' => now()->subDays(30),
                'updated_at' => now(),
            ],
            // Gerente da primeira empresa
            [
                'name' => 'Carlos Silva',
                'email' => 'carlos.silva@techcorp.com',
                'password' => Hash::make('gerente123'),
                'phone' => '(11) 98888-1111',
                'id_comp' => $company1->id,
                'tipo' => 'comum',
                'created_at' => now()->subDays(25),
                'updated_at' => now(),
            ],
            // Supervisor de manutenção
            [
                'name' => 'Ana Santos',
                'email' => 'ana.santos@techcorp.com',
                'password' => Hash::make('supervisor123'),
                'phone' => '(11) 97777-2222',
                'id_comp' => $company1->id,
                'tipo' => 'comum',
                'created_at' => now()->subDays(20),
                'updated_at' => now(),
            ],
            // Técnico de qualidade
            [
                'name' => 'Pedro Oliveira',
                'email' => 'pedro.oliveira@techcorp.com',
                'password' => Hash::make('tecnico123'),
                'phone' => '(11) 96666-3333',
                'id_comp' => $company1->id,
                'tipo' => 'comum',
                'created_at' => now()->subDays(15),
                'updated_at' => now(),
            ],
            // Operador da Linha 1
            [
                'name' => 'Maria Costa',
                'email' => 'maria.costa@techcorp.com',
                'password' => Hash::make('operador123'),
                'phone' => '(11) 95555-4444',
                'id_comp' => $company1->id,
                'tipo' => 'comum',
                'created_at' => now()->subDays(10),
                'updated_at' => now(),
            ],
            // Operador da Linha 2
            [
                'name' => 'João Ferreira',
                'email' => 'joao.ferreira@techcorp.com',
                'password' => Hash::make('operador123'),
                'phone' => '(11) 94444-5555',
                'id_comp' => $company1->id,
                'tipo' => 'comum',
                'created_at' => now()->subDays(8),
                'updated_at' => now(),
            ],
            // Técnico de manutenção preventiva
            [
                'name' => 'Roberto Lima',
                'email' => 'roberto.lima@techcorp.com',
                'password' => Hash::make('tecnico123'),
                'phone' => '(11) 93333-6666',
                'id_comp' => $company1->id,
                'tipo' => 'comum',
                'created_at' => now()->subDays(6),
                'updated_at' => now(),
            ],
        ];

        // Se há uma segunda empresa, adicionar usuários para ela
        if ($company2->id !== $company1->id) {
            $company2Users = [
                [
                    'name' => 'Fernanda Rodrigues',
                    'email' => 'fernanda.rodrigues@manufatura.com',
                    'password' => Hash::make('gerente123'),
                    'phone' => '(21) 98888-7777',
                    'id_comp' => $company2->id,
                    'tipo' => 'comum',
                    'created_at' => now()->subDays(12),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Lucas Almeida',
                    'email' => 'lucas.almeida@manufatura.com',
                    'password' => Hash::make('supervisor123'),
                    'phone' => '(21) 97777-8888',
                    'id_comp' => $company2->id,
                    'tipo' => 'comum',
                    'created_at' => now()->subDays(9),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Carla Mendes',
                    'email' => 'carla.mendes@manufatura.com',
                    'password' => Hash::make('tecnico123'),
                    'phone' => '(21) 96666-9999',
                    'id_comp' => $company2->id,
                    'tipo' => 'comum',
                    'created_at' => now()->subDays(5),
                    'updated_at' => now(),
                ],
            ];
            
            $users = array_merge($users, $company2Users);
        }

        // Adicionar alguns usuários com diferentes status
        $additionalUsers = [
            [
                'name' => 'Supervisor Geral',
                'email' => 'supervisor@empresa.com',
                'password' => Hash::make('supervisor123'),
                'phone' => '(11) 91111-0000',
                'id_comp' => $company1->id,
                'tipo' => 'admin',
                'created_at' => now()->subDays(60),
                'updated_at' => now()->subDays(30),
            ],
        ];

        $users = array_merge($users, $additionalUsers);

        // Inserir todos os usuários
        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info("✅ Users seeded successfully! Created " . count($users) . " users.");
        $this->command->info("📧 Credenciais de exemplo:");
        $this->command->info("   Admin: admin@sistema.com / admin123");
        $this->command->info("   Gerente: carlos.silva@techcorp.com / gerente123");
        $this->command->info("   Supervisor: ana.santos@techcorp.com / supervisor123");
        $this->command->info("   Técnico: pedro.oliveira@techcorp.com / tecnico123");
        $this->command->info("   Operador: maria.costa@techcorp.com / operador123");
    }
}
