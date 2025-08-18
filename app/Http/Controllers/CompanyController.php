<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CompanyController extends Controller
{
    /**
     * Listar todas as companhias
     */
    public function index(): JsonResponse
    {
        try {
            // Buscar companhias diretamente do banco
            $companies = \DB::table('companies')->get();

            // Adicionar contagem de departamentos para cada companhia
            foreach ($companies as $company) {
                $company->departments_count = \DB::table('departments')
                    ->where('id_comp', $company->id)
                    ->count();
            }

            return response()->json([
                'success' => true,
                'data' => $companies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar companhias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter uma companhia específica
     */
    public function show(int $id): JsonResponse
    {
        try {
            $company = Company::with(['departments' => function ($query) {
                $query->orderBy('nivel_hierarquico')->orderBy('name');
            }])->find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Companhia não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $company
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter companhia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar nova companhia
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:companies,name'
            ]);

            $company = Company::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Companhia criada com sucesso',
                'data' => $company
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar companhia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar companhia
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Companhia não encontrada'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:companies,name,' . $id
            ]);

            $company->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Companhia atualizada com sucesso',
                'data' => $company
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar companhia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar companhia
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Companhia não encontrada'
                ], 404);
            }

            // Verificar se tem departamentos
            if ($company->departments()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível deletar companhia com departamentos'
                ], 422);
            }

            $company->delete();

            return response()->json([
                'success' => true,
                'message' => 'Companhia deletada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar companhia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estrutura organizacional da companhia
     */
    public function organizationalStructure(int $id): JsonResponse
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Companhia não encontrada'
                ], 404);
            }

            $structure = $company->getOrganizationalStructure();
            $stats = Department::getOrganizationalStats($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'company' => $company,
                    'structure' => $structure,
                    'statistics' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estrutura organizacional',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter árvore organizacional da companhia
     */
    public function organizationalTree(int $id): JsonResponse
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Companhia não encontrada'
                ], 404);
            }

            $tree = Department::getOrganizationalTree($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'company' => $company,
                    'tree' => $tree
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter árvore organizacional',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
