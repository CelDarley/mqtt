<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'TechCorp Indústria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Manufatura Avançada Ltda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'AutoParts Brasil',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Smart Factory Solutions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Indústria 4.0 Inovações',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }

        $this->command->info('✅ Companies seeded successfully!');
    }
}
