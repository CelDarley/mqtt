<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    private $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = config('app.api_base_url', 'http://localhost:8000/api');
    }

    /**
     * Listar todos os departamentos
     */
    public function index(Request $request)
    {
        try {
            $response = Http::get($this->apiBaseUrl . '/mqtt/departments', [
                'company_id' => $request->get('company_id'),
                'nivel_hierarquico' => $request->get('nivel_hierarquico'),
            ]);

            if ($response->successful()) {
                $departments = $response->json()['data'] ?? [];
                
                // Organizar departamentos hierarquicamente
                $departments = $this->organizeHierarchically($departments);
                
                // Buscar também as companhias para o filtro
                $companiesResponse = Http::get($this->apiBaseUrl . '/mqtt/companies');
                $companies = $companiesResponse->successful() ? $companiesResponse->json()['data'] ?? [] : [];

                return view('departments.index', compact('departments', 'companies'));
            }

            return view('departments.index')->with('error', 'Erro ao carregar departamentos');
        } catch (\Exception $e) {
            Log::error('Erro ao listar departamentos: ' . $e->getMessage());
            return view('departments.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        try {
            // Buscar companhias para o select
            $companiesResponse = Http::get($this->apiBaseUrl . '/mqtt/companies');
            $companies = $companiesResponse->successful() ? $companiesResponse->json()['data'] ?? [] : [];

            // Buscar departamentos para unidade superior
            $departmentsResponse = Http::get($this->apiBaseUrl . '/mqtt/departments');
            $departments = $departmentsResponse->successful() ? $departmentsResponse->json()['data'] ?? [] : [];

            return view('departments.create', compact('companies', 'departments'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação: ' . $e->getMessage());
            return redirect()->route('departments.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Armazenar novo departamento
     */
    public function store(Request $request)
    {
        try {
            $data = [
                'name' => $request->name,
                'nivel_hierarquico' => $request->nivel_hierarquico,
                'id_unid_up' => $request->id_unid_up ?: null,
                'id_comp' => $request->id_comp,
            ];
            
            Log::info('Dados enviados para API:', $data);
            
            $response = Http::post($this->apiBaseUrl . '/mqtt/departments', $data);
            
            Log::info('Resposta da API:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return redirect()->route('departments.index')->with('success', 'Departamento criado com sucesso!');
            }

            $responseData = $response->json();
            $errorMessage = $responseData['message'] ?? 'Erro ao criar departamento';
            
            // Log detalhado do erro
            Log::error('Erro na API de departamento:', [
                'status' => $response->status(),
                'response' => $responseData,
                'sent_data' => $data
            ]);
            
            return back()->withInput()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao criar departamento: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Mostrar detalhes do departamento
     */
    public function show($id)
    {
        try {
            $response = Http::get($this->apiBaseUrl . "/departments/{$id}");

            if ($response->successful()) {
                $department = $response->json()['data'];
                return view('departments.show', compact('department'));
            }

            return redirect()->route('departments.index')->with('error', 'Departamento não encontrado');
        } catch (\Exception $e) {
            Log::error('Erro ao mostrar departamento: ' . $e->getMessage());
            return redirect()->route('departments.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Mostrar formulário de edição
     */
    public function edit($id)
    {
        try {
            $response = Http::get($this->apiBaseUrl . "/departments/{$id}");
            
            if ($response->successful()) {
                $department = $response->json()['data'];

                // Buscar companhias para o select
                $companiesResponse = Http::get($this->apiBaseUrl . '/mqtt/companies');
                $companies = $companiesResponse->successful() ? $companiesResponse->json()['data'] ?? [] : [];

                // Buscar departamentos para unidade superior (excluindo o próprio)
                $departmentsResponse = Http::get($this->apiBaseUrl . '/departments');
                $allDepartments = $departmentsResponse->successful() ? $departmentsResponse->json()['data'] ?? [] : [];
                $departments = array_filter($allDepartments, function($dept) use ($id) {
                    return $dept['id'] != $id;
                });

                return view('departments.edit', compact('department', 'companies', 'departments'));
            }

            return redirect()->route('departments.index')->with('error', 'Departamento não encontrado');
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return redirect()->route('departments.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Atualizar departamento
     */
    public function update(Request $request, $id)
    {
        try {
            $response = Http::put($this->apiBaseUrl . "/departments/{$id}", [
                'name' => $request->name,
                'nivel_hierarquico' => $request->nivel_hierarquico,
                'id_unid_up' => $request->id_unid_up ?: null,
                'id_comp' => $request->id_comp,
            ]);

            if ($response->successful()) {
                return redirect()->route('departments.index')->with('success', 'Departamento atualizado com sucesso!');
            }

            $errorMessage = $response->json()['message'] ?? 'Erro ao atualizar departamento';
            return back()->withInput()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar departamento: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Deletar departamento
     */
    public function destroy($id)
    {
        try {
            $response = Http::delete($this->apiBaseUrl . "/departments/{$id}");

            if ($response->successful()) {
                return redirect()->route('departments.index')->with('success', 'Departamento deletado com sucesso!');
            }

            $errorMessage = $response->json()['message'] ?? 'Erro ao deletar departamento';
            return redirect()->route('departments.index')->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar departamento: ' . $e->getMessage());
            return redirect()->route('departments.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Organizar departamentos hierarquicamente
     */
    private function organizeHierarchically($departments)
    {
        // Converter para collection para facilitar manipulação
        $departments = collect($departments);
        
        // Separar por empresa
        $organizedByCompany = $departments->groupBy('id_comp');
        
        $result = [];
        
        foreach ($organizedByCompany as $companyId => $companyDepartments) {
            // Criar um mapa de departamentos por ID
            $departmentMap = $companyDepartments->keyBy('id');
            
            // Função recursiva para construir a árvore
            $buildTree = function($parentId = null, $level = 1) use ($departmentMap, &$buildTree) {
                $children = [];
                
                foreach ($departmentMap as $dept) {
                    if ($dept['id_unid_up'] == $parentId) {
                        $dept['nivel_hierarquico'] = $level;
                        $children[] = $dept;
                        
                        // Adicionar filhos recursivamente
                        $subChildren = $buildTree($dept['id'], $level + 1);
                        $children = array_merge($children, $subChildren);
                    }
                }
                
                return $children;
            };
            
            // Construir árvore para esta empresa (começando pelos departamentos raiz)
            $companyTree = $buildTree(null, 1);
            $result = array_merge($result, $companyTree);
        }
        
        return $result;
    }
}
