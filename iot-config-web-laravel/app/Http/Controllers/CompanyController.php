<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    private $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = config('app.api_base_url', 'http://localhost:8000/api');
    }

    /**
     * Listar todas as empresas
     */
    public function index(Request $request)
    {
        try {
            $response = Http::get($this->apiBaseUrl . '/mqtt/companies', [
                'search' => $request->get('search'),
            ]);

            if ($response->successful()) {
                $companies = $response->json()['data'] ?? [];
                
                // Debug temporário
                if (count($companies) > 0) {
                    Log::info('First company data type: ' . gettype($companies[0]));
                    Log::info('First company data: ', $companies[0]);
                }
                
                // Garantir que são arrays
                $companies = array_map(function($company) {
                    return is_array($company) ? $company : (array) $company;
                }, $companies);
                
                return view('companies.index', compact('companies'));
            }

            return view('companies.index')->with('error', 'Erro ao carregar empresas');
        } catch (\Exception $e) {
            Log::error('Erro ao listar empresas: ' . $e->getMessage());
            return view('companies.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Armazenar nova empresa
     */
    public function store(Request $request)
    {
        try {
            $response = Http::post($this->apiBaseUrl . '/mqtt/companies', [
                'name' => $request->name,
            ]);

            if ($response->successful()) {
                return redirect()->route('companies.index')->with('success', 'Empresa criada com sucesso!');
            }

            $responseData = $response->json();
            $errorMessage = $responseData['message'] ?? 'Erro ao criar empresa';
            
            // Se há erros de validação específicos, inclui-los na mensagem
            if (isset($responseData['errors'])) {
                $validationErrors = [];
                foreach ($responseData['errors'] as $field => $errors) {
                    $validationErrors[] = implode(', ', $errors);
                }
                $errorMessage .= ' - ' . implode('; ', $validationErrors);
            }
            
            return back()->withInput()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao criar empresa: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Mostrar empresa específica
     */
    public function show($id)
    {
        try {
            $response = Http::get($this->apiBaseUrl . "/mqtt/companies/{$id}");

            if ($response->successful()) {
                $company = $response->json()['data'];
                
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json(['success' => true, 'company' => $company]);
                }
                
                return view('companies.show', compact('company'));
            } else {
                return redirect()->route('companies.index')->with('error', 'Empresa não encontrada');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao mostrar empresa: ' . $e->getMessage());
            return redirect()->route('companies.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Mostrar formulário de edição
     */
    public function edit($id)
    {
        try {
            $response = Http::get($this->apiBaseUrl . "/mqtt/companies/{$id}");
            
            if ($response->successful()) {
                $company = $response->json()['data'];
                return view('companies.edit', compact('company'));
            } else {
                return redirect()->route('companies.index')->with('error', 'Empresa não encontrada');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao editar empresa: ' . $e->getMessage());
            return redirect()->route('companies.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Atualizar empresa
     */
    public function update(Request $request, $id)
    {
        try {
            $response = Http::put($this->apiBaseUrl . "/mqtt/companies/{$id}", [
                'name' => $request->name,
            ]);

            if ($response->successful()) {
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json(['success' => true, 'message' => 'Empresa atualizada com sucesso!']);
                }
                return redirect()->route('companies.index')->with('success', 'Empresa atualizada com sucesso!');
            }

            $responseData = $response->json();
            $errorMessage = $responseData['message'] ?? 'Erro ao atualizar empresa';
            
            if (isset($responseData['errors'])) {
                $validationErrors = [];
                foreach ($responseData['errors'] as $field => $errors) {
                    $validationErrors[] = implode(', ', $errors);
                }
                $errorMessage .= ' - ' . implode('; ', $validationErrors);
            }

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 400);
            }
            
            return back()->withInput()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar empresa: ' . $e->getMessage());
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Erro de conexão com a API'], 500);
            }
            
            return back()->withInput()->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Deletar empresa
     */
    public function destroy($id)
    {
        try {
            $response = Http::delete($this->apiBaseUrl . "/mqtt/companies/{$id}");

            if ($response->successful()) {
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json(['success' => true, 'message' => 'Empresa deletada com sucesso!']);
                }
                return redirect()->route('companies.index')->with('success', 'Empresa deletada com sucesso!');
            }

            $responseData = $response->json();
            $errorMessage = $responseData['message'] ?? 'Erro ao deletar empresa';
            
            if (request()->wantsJson() || request()->ajax()) {
                // Retornar os dados do backend, incluindo departamentos se houver
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'departments' => $responseData['departments'] ?? null,
                    'departments_count' => $responseData['departments_count'] ?? 0
                ], $response->status());
            }
            
            return redirect()->route('companies.index')->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar empresa: ' . $e->getMessage());
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Erro de conexão com a API'], 500);
            }
            
            return redirect()->route('companies.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Obter estrutura organizacional da empresa
     */
    public function organizationalStructure($id)
    {
        try {
            $response = Http::get($this->apiBaseUrl . "/mqtt/companies/{$id}/organizational-structure");

            if ($response->successful()) {
                $data = $response->json()['data'];
                return view('companies.organizational-structure', compact('data'));
            } else {
                return redirect()->route('companies.index')->with('error', 'Empresa não encontrada');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao obter estrutura organizacional: ' . $e->getMessage());
            return redirect()->route('companies.index')->with('error', 'Erro de conexão com a API');
        }
    }
} 