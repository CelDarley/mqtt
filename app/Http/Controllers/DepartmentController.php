<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    /**
     * Listar todos os departamentos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Department::with(['company', 'parent']);

            // Filtrar por companhia
            if ($request->has('company_id')) {
                $query->where('id_comp', $request->company_id);
            }

            // Filtrar por nível hierárquico
            if ($request->has('nivel_hierarquico')) {
                $query->where('nivel_hierarquico', $request->nivel_hierarquico);
            }

            // Filtrar por unidade superior
            if ($request->has('id_unid_up')) {
                $query->where('id_unid_up', $request->id_unid_up);
            }

            $departments = $query->orderBy('nivel_hierarquico')
                                ->orderBy('name')
                                ->get();

            return response()->json([
                'success' => true,
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar departamentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter um departamento específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $department = Department::with(['company', 'parent', 'children'])
                                  ->find($id);

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamento não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $department
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar novo departamento
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'nivel_hierarquico' => 'required|integer|min:1',
                'id_unid_up' => 'nullable|integer|exists:departments,id',
                'id_comp' => 'required|integer|exists:companies,id'
            ]);

            // Validações de negócio
            if ($validated['nivel_hierarquico'] > 1 && !isset($validated['id_unid_up'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamentos de nível superior a 1 devem ter uma unidade superior'
                ], 422);
            }

            if (isset($validated['id_unid_up']) && $validated['id_unid_up']) {
                $parentDepartment = Department::find($validated['id_unid_up']);

                // Verificar se a unidade superior pertence à mesma companhia
                if ($parentDepartment->id_comp != $validated['id_comp']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A unidade superior deve pertencer à mesma companhia'
                    ], 422);
                }

                // Verificar se o nível hierárquico é válido
                if ($validated['nivel_hierarquico'] != $parentDepartment->nivel_hierarquico + 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'O nível hierárquico deve ser o nível da unidade superior + 1'
                    ], 422);
                }
            } else {
                // Se não tem unidade superior, deve ser nível 1
                if ($validated['nivel_hierarquico'] != 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Departamentos sem unidade superior devem ser de nível 1'
                    ], 422);
                }
            }

            $department = Department::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Departamento criado com sucesso',
                'data' => $department->load(['company', 'parent'])
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
                'message' => 'Erro ao criar departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar departamento
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamento não encontrado'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'nivel_hierarquico' => 'required|integer|min:1',
                'id_unid_up' => 'nullable|integer|exists:departments,id',
                'id_comp' => 'required|integer|exists:companies,id'
            ]);

            // Validações de negócio
            if ($validated['nivel_hierarquico'] > 1 && !isset($validated['id_unid_up'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamentos de nível superior a 1 devem ter uma unidade superior'
                ], 422);
            }

            if (isset($validated['id_unid_up']) && $validated['id_unid_up']) {
                $parentDepartment = Department::find($validated['id_unid_up']);

                // Verificar se a unidade superior pertence à mesma companhia
                if ($parentDepartment->id_comp != $validated['id_comp']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A nova unidade superior deve pertencer à mesma companhia'
                    ], 422);
                }

                // Verificar se o nível hierárquico é válido
                if ($validated['nivel_hierarquico'] != $parentDepartment->nivel_hierarquico + 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'O nível hierárquico deve ser o nível da unidade superior + 1'
                    ], 422);
                }

                // Verificar se não está tentando se referenciar a si mesmo
                if ($validated['id_unid_up'] == $id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Um departamento não pode ser sua própria unidade superior'
                    ], 422);
                }
            } else {
                // Se não tem unidade superior, deve ser nível 1
                if ($validated['nivel_hierarquico'] != 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Departamentos sem unidade superior devem ser de nível 1'
                    ], 422);
                }
            }

            $department->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Departamento atualizado com sucesso',
                'data' => $department->load(['company', 'parent'])
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
                'message' => 'Erro ao atualizar departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar departamento
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamento não encontrado'
                ], 404);
            }

            // Verificar se tem departamentos subordinados
            if ($department->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível deletar departamento com unidades subordinadas'
                ], 422);
            }

            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Departamento deletado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estrutura hierárquica de um departamento
     */
    public function hierarchy(int $id): JsonResponse
    {
        try {
            $department = Department::with(['company', 'parent', 'children'])
                                  ->find($id);

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamento não encontrado'
                ], 404);
            }

            $hierarchyPath = $department->getHierarchyPath();
            $siblings = $department->getSiblings()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'department' => $department,
                    'hierarchy_path' => $hierarchyPath,
                    'siblings' => $siblings,
                    'is_root' => $department->isRoot(),
                    'is_leaf' => $department->isLeaf()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter hierarquia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter departamentos por companhia
     */
    public function byCompany(int $companyId): JsonResponse
    {
        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Companhia não encontrada'
                ], 404);
            }

            $departments = Department::where('id_comp', $companyId)
                                   ->with(['parent'])
                                   ->orderBy('nivel_hierarquico')
                                   ->orderBy('name')
                                   ->get();

            $structure = $departments->groupBy('nivel_hierarquico');
            $stats = Department::getOrganizationalStats($companyId);

            return response()->json([
                'success' => true,
                'data' => [
                    'company' => $company,
                    'departments' => $departments,
                    'structure' => $structure,
                    'statistics' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter departamentos da companhia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mover departamento na hierarquia
     */
    public function move(Request $request, int $id): JsonResponse
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departamento não encontrado'
                ], 404);
            }

            $validated = $request->validate([
                'new_parent_id' => 'nullable|integer|exists:departments,id',
                'new_level' => 'required|integer|min:1'
            ]);

            // Verificar se não está tentando se mover para si mesmo
            if (isset($validated['new_parent_id']) && $validated['new_parent_id'] == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Um departamento não pode ser sua própria unidade superior'
                ], 422);
            }

            // Verificar se o novo pai pertence à mesma companhia
            if (isset($validated['new_parent_id']) && $validated['new_parent_id']) {
                $newParent = Department::find($validated['new_parent_id']);
                if ($newParent->id_comp != $department->id_comp) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A nova unidade superior deve pertencer à mesma companhia'
                    ], 422);
                }
            }

            // Atualizar departamento
            $department->update([
                'id_unid_up' => $validated['new_parent_id'] ?? null,
                'nivel_hierarquico' => $validated['new_level']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Departamento movido com sucesso',
                'data' => $department->load(['company', 'parent'])
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
                'message' => 'Erro ao mover departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
