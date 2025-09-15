<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Company;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar algumas empresas para associar departamentos
        $companies = Company::all();
        
        if ($companies->isEmpty()) {
            $this->command->error('❌ Nenhuma empresa encontrada. Execute CompanySeeder primeiro.');
            return;
        }

        $company1 = $companies->first();
        $company2 = $companies->count() > 1 ? $companies->skip(1)->first() : $company1;

        // Estrutura hierárquica para primeira empresa
        $departments = [
            // Nível 1 - Departamentos raiz
            [
                'name' => 'Produção',
                'nivel_hierarquico' => 1,
                'id_unid_up' => null,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Manutenção',
                'nivel_hierarquico' => 1,
                'id_unid_up' => null,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Qualidade',
                'nivel_hierarquico' => 1,
                'id_unid_up' => null,
                'id_comp' => $company1->id,
            ],
        ];

        // Criar departamentos de nível 1
        $createdDepts = [];
        foreach ($departments as $dept) {
            $created = Department::create($dept);
            $createdDepts[$created->name] = $created;
        }

        // Nível 2 - Subdepartamentos
        $subDepartments = [
            [
                'name' => 'Linha 1',
                'nivel_hierarquico' => 2,
                'id_unid_up' => $createdDepts['Produção']->id,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Linha 2',
                'nivel_hierarquico' => 2,
                'id_unid_up' => $createdDepts['Produção']->id,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Montagem',
                'nivel_hierarquico' => 2,
                'id_unid_up' => $createdDepts['Produção']->id,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Manutenção Preventiva',
                'nivel_hierarquico' => 2,
                'id_unid_up' => $createdDepts['Manutenção']->id,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Manutenção Corretiva',
                'nivel_hierarquico' => 2,
                'id_unid_up' => $createdDepts['Manutenção']->id,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Controle de Qualidade',
                'nivel_hierarquico' => 2,
                'id_unid_up' => $createdDepts['Qualidade']->id,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Laboratório',
                'nivel_hierarquico' => 2,
                'id_unid_up' => $createdDepts['Qualidade']->id,
                'id_comp' => $company1->id,
            ],
        ];

        foreach ($subDepartments as $dept) {
            $created = Department::create($dept);
            $createdDepts[$created->name] = $created;
        }

        // Nível 3 - Sub-subdepartamentos
        $subSubDepartments = [
            [
                'name' => 'Setor A',
                'nivel_hierarquico' => 3,
                'id_unid_up' => $createdDepts['Linha 1']->id,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Setor B',
                'nivel_hierarquico' => 3,
                'id_unid_up' => $createdDepts['Linha 1']->id,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Estação 1',
                'nivel_hierarquico' => 3,
                'id_unid_up' => $createdDepts['Montagem']->id,
                'id_comp' => $company1->id,
            ],
            [
                'name' => 'Estação 2',
                'nivel_hierarquico' => 3,
                'id_unid_up' => $createdDepts['Montagem']->id,
                'id_comp' => $company1->id,
            ],
        ];

        foreach ($subSubDepartments as $dept) {
            Department::create($dept);
        }

        // Estrutura para segunda empresa (mais simples)
        if ($company2->id !== $company1->id) {
            $company2Departments = [
                [
                    'name' => 'Fabricação',
                    'nivel_hierarquico' => 1,
                    'id_unid_up' => null,
                    'id_comp' => $company2->id,
                ],
                [
                    'name' => 'Logística',
                    'nivel_hierarquico' => 1,
                    'id_unid_up' => null,
                    'id_comp' => $company2->id,
                ],
            ];

            $company2CreatedDepts = [];
            foreach ($company2Departments as $dept) {
                $created = Department::create($dept);
                $company2CreatedDepts[$created->name] = $created;
            }

            // Subdepartamentos da segunda empresa
            $company2SubDepartments = [
                [
                    'name' => 'Usinagem',
                    'nivel_hierarquico' => 2,
                    'id_unid_up' => $company2CreatedDepts['Fabricação']->id,
                    'id_comp' => $company2->id,
                ],
                [
                    'name' => 'Soldagem',
                    'nivel_hierarquico' => 2,
                    'id_unid_up' => $company2CreatedDepts['Fabricação']->id,
                    'id_comp' => $company2->id,
                ],
                [
                    'name' => 'Expedição',
                    'nivel_hierarquico' => 2,
                    'id_unid_up' => $company2CreatedDepts['Logística']->id,
                    'id_comp' => $company2->id,
                ],
                [
                    'name' => 'Recebimento',
                    'nivel_hierarquico' => 2,
                    'id_unid_up' => $company2CreatedDepts['Logística']->id,
                    'id_comp' => $company2->id,
                ],
            ];

            foreach ($company2SubDepartments as $dept) {
                Department::create($dept);
            }
        }

        $this->command->info('✅ Departments seeded successfully with hierarchical structure!');
    }
}
