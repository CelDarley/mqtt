<?php

namespace App\Http\Controllers;

use App\Models\DeviceType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DeviceTypeController extends Controller
{
    /**
     * Listar todos os tipos de dispositivo
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DeviceType::query();

            // Filtrar apenas ativos se solicitado
            if ($request->get('active_only')) {
                $query->active();
            }

            // Buscar por nome se fornecido
            if ($request->has('search')) {
                $query->byName($request->search);
            }

            $deviceTypes = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $deviceTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar tipos de dispositivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter um tipo de dispositivo específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $deviceType = DeviceType::find($id);

            if (!$deviceType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de dispositivo não encontrado'
                ], 404);
            }

            $stats = $deviceType->getStats();

            return response()->json([
                'success' => true,
                'data' => array_merge($deviceType->toArray(), ['stats' => $stats])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter tipo de dispositivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar novo tipo de dispositivo
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:device_types,name',
                'description' => 'nullable|string|max:500',
                'icon' => 'nullable|string|max:100',
                'specifications' => 'nullable|array',
                'is_active' => 'boolean'
            ]);

            $deviceType = DeviceType::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de dispositivo criado com sucesso',
                'data' => $deviceType
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
                'message' => 'Erro ao criar tipo de dispositivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar tipo de dispositivo
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $deviceType = DeviceType::find($id);

            if (!$deviceType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de dispositivo não encontrado'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:device_types,name,' . $id,
                'description' => 'nullable|string|max:500',
                'icon' => 'nullable|string|max:100',
                'specifications' => 'nullable|array',
                'is_active' => 'boolean'
            ]);

            $deviceType->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de dispositivo atualizado com sucesso',
                'data' => $deviceType
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
                'message' => 'Erro ao atualizar tipo de dispositivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar tipo de dispositivo
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deviceType = DeviceType::find($id);

            if (!$deviceType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de dispositivo não encontrado'
                ], 404);
            }

            // Verificar se tem tópicos associados
            if ($deviceType->getRelatedTopics()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível deletar tipo de dispositivo com tópicos associados'
                ], 422);
            }

            $deviceType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de dispositivo deletado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar tipo de dispositivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ativar/desativar tipo de dispositivo
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $deviceType = DeviceType::find($id);

            if (!$deviceType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de dispositivo não encontrado'
                ], 404);
            }

            $deviceType->update(['is_active' => !$deviceType->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
                'data' => $deviceType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas dos tipos de dispositivo
     */
    public function stats(): JsonResponse
    {
        try {
            $totalTypes = DeviceType::count();
            $activeTypes = DeviceType::active()->count();
            $inactiveTypes = $totalTypes - $activeTypes;
            
            // Contar tipos com tópicos (método simplificado)
            $typesWithTopics = 0;
            foreach (DeviceType::all() as $type) {
                if ($type->getRelatedTopics()->count() > 0) {
                    $typesWithTopics++;
                }
            }
            $typesWithoutTopics = $totalTypes - $typesWithTopics;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_types' => $totalTypes,
                    'active_types' => $activeTypes,
                    'inactive_types' => $inactiveTypes,
                    'types_with_topics' => $typesWithTopics,
                    'types_without_topics' => $typesWithoutTopics,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
