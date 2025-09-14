<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando o seeding completo do sistema MQTT IoT...');
        $this->command->newLine();

        // Ordem de execução é importante devido às dependências
        $this->command->info('📊 1/5 - Criando empresas...');
        $this->call(CompanySeeder::class);
        $this->command->newLine();

        $this->command->info('🏢 2/5 - Criando departamentos com hierarquia...');
        $this->call(DepartmentSeeder::class);
        $this->command->newLine();

        $this->command->info('📱 3/5 - Criando tipos de dispositivos IoT...');
        $this->call(DeviceTypeSeeder::class);
        $this->command->newLine();

        $this->command->info('👥 4/5 - Criando usuários do sistema...');
        $this->call(UserSeeder::class);
        $this->command->newLine();

        $this->command->info('📡 5/5 - Criando tópicos MQTT...');
        $this->call(TopicSeeder::class);
        $this->command->newLine();

        $this->command->info('🎉 Seeding completo finalizado com sucesso!');
        $this->command->newLine();
        
        // Exibir resumo
        $this->displaySummary();
    }

    private function displaySummary(): void
    {
        $this->command->info('📋 RESUMO DO SISTEMA POPULADO:');
        $this->command->info('════════════════════════════════');
        
        $companies = \App\Models\Company::count();
        $departments = \App\Models\Department::count();
        $deviceTypes = \App\Models\DeviceType::count();
        $users = \App\Models\User::count();
        $topics = \App\Models\Topic::count();
        
        $this->command->info("🏢 Empresas: {$companies}");
        $this->command->info("🏗️  Departamentos: {$departments}");
        $this->command->info("📱 Tipos de Dispositivos: {$deviceTypes}");
        $this->command->info("👥 Usuários: {$users}");
        $this->command->info("📡 Tópicos MQTT: {$topics}");
        $this->command->newLine();
        
        $this->command->info('🔐 CREDENCIAIS DE ACESSO:');
        $this->command->info('════════════════════════');
        $this->command->info('Admin Geral:     admin@sistema.com / admin123');
        $this->command->info('Gerente:         carlos.silva@techcorp.com / gerente123');
        $this->command->info('Supervisor:      ana.santos@techcorp.com / supervisor123');
        $this->command->info('Técnico:         pedro.oliveira@techcorp.com / tecnico123');
        $this->command->info('Operador:        maria.costa@techcorp.com / operador123');
        $this->command->newLine();
        
        $this->command->info('🌐 ACESSO ÀS APLICAÇÕES:');
        $this->command->info('════════════════════════');
        $this->command->info('📊 Dashboard Web:   http://10.102.0.101:8001');
        $this->command->info('📱 App Config:      http://10.102.0.101:8002');
        $this->command->info('🔧 API Backend:     http://10.102.0.101:8000/api');
        $this->command->newLine();
        
        $this->command->info('✨ Sistema pronto para uso!');
    }
}
