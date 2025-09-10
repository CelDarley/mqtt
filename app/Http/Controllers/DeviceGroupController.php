<?php

namespace App\Http\Controllers;

use App\Models\DeviceGroup;
use App\Models\DeviceGroupAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class DeviceGroupController extends Controller
{
    /**
     * Listar todos os grupos
     */
    public function index(): JsonResponse
    {
        try {
            $groups = DeviceGroup::active()->ordered()->get();

            return response()->json([
                'success' => true,
                'message' => 'Grupos carregados com sucesso',
                'data' => $groups
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar grupos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar um novo grupo
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:device_groups,name',
                'description' => 'nullable|string',
                'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
                'sort_order' => 'nullable|integer|min:0'
            ]);

            $group = DeviceGroup::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Grupo criado com sucesso',
                'data' => $group
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar grupo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar um grupo específico
     */
    public function show($id): JsonResponse
    {
        try {
            $group = DeviceGroup::with(['assignments.device'])->find($id);

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Grupo carregado com sucesso',
                'data' => $group
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar grupo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar um grupo
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $group = DeviceGroup::find($id);

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo não encontrado'
                ], 404);
            }

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('device_groups')->ignore($id)],
                'description' => 'nullable|string',
                'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);

            $group->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Grupo atualizado com sucesso',
                'data' => $group
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar grupo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover um grupo
     */
    public function destroy($id): JsonResponse
    {
        try {
            $group = DeviceGroup::find($id);

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo não encontrado'
                ], 404);
            }

            // Verifica se há dispositivos associados
            $hasDevices = $group->assignments()->active()->exists();

            if ($hasDevices) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível remover um grupo que possui dispositivos associados'
                ], 422);
            }

            $group->delete();

            return response()->json([
                'success' => true,
                'message' => 'Grupo removido com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover grupo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atribuir dispositivo a um grupo
     */
    public function assignDevice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|exists:topics,id',
                'group_id' => 'nullable|exists:device_groups,id'
            ]);

            // Remove associação anterior se existir
            DeviceGroupAssignment::where('device_id', $validated['device_id'])
                ->update(['is_active' => false]);

            // Cria nova associação (ou remove se group_id for null)
            if ($validated['group_id']) {
                DeviceGroupAssignment::create([
                    'device_id' => $validated['device_id'],
                    'group_id' => $validated['group_id'],
                    'is_active' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dispositivo atribuído ao grupo com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atribuir dispositivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar dispositivos de um grupo
     */
    public function devices($id): JsonResponse
    {
        try {
            $group = DeviceGroup::with(['assignments.device'])->find($id);

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo não encontrado'
                ], 404);
            }

            $devices = $group->devices;

            return response()->json([
                'success' => true,
                'message' => 'Dispositivos do grupo carregados com sucesso',
                'data' => $devices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dispositivos: ' . $e->getMessage()
            ], 500);
        }
    }
}
