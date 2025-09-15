<?php

namespace App\Http\Controllers;

use App\Models\DeviceType;
use App\Models\OtaUpdateLog;
use App\Services\OtaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OtaController extends Controller
{
    private OtaService $otaService;

    public function __construct(OtaService $otaService)
    {
        $this->otaService = $otaService;
    }

    /**
     * Iniciar atualização OTA para um tipo de dispositivo
     * POST /api/mqtt/device-types/{id}/ota-update
     */
    public function triggerUpdate(Request $request, int $deviceTypeId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'force_update' => 'boolean',
            'user_id' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->otaService->triggerOtaUpdate($deviceTypeId, $request->all());

        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Buscar informações de firmware disponível
     * GET /api/mqtt/device-types/{id}/firmware-info
     */
    public function getFirmwareInfo(int $deviceTypeId): JsonResponse
    {
        $deviceType = DeviceType::find($deviceTypeId);
        
        if (!$deviceType) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de dispositivo não encontrado'
            ], 404);
        }

        $firmwareInfo = $this->otaService->getFirmwareInfo($deviceType);
        
        return response()->json([
            'success' => true,
            'device_type' => $deviceType->name,
            'firmware_info' => $firmwareInfo
        ]);
    }

    /**
     * Buscar status de um update OTA específico
     * GET /api/mqtt/ota-updates/{id}
     */
    public function getUpdateStatus(int $otaLogId): JsonResponse
    {
        return response()->json($this->otaService->getOtaStatus($otaLogId));
    }

    /**
     * Listar updates OTA
     * GET /api/mqtt/ota-updates
     */
    public function listUpdates(Request $request): JsonResponse
    {
        $query = OtaUpdateLog::with('deviceType');

        // Filtros
        if ($request->has('device_type_id')) {
            $query->where('device_type_id', $request->device_type_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('days')) {
            $days = (int) $request->days;
            $query->where('created_at', '>=', now()->subDays($days));
        }

        // Ordenação
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginação
        $perPage = min((int) $request->get('per_page', 15), 100);
        $updates = $query->paginate($perPage);

        // Adicionar estatísticas
        $updates->getCollection()->transform(function ($update) {
            return [
                'id' => $update->id,
                'device_type' => $update->deviceType->name,
                'firmware_version' => $update->firmware_version,
                'status' => $update->status,
                'devices_count' => $update->devices_count,
                'successful_devices' => $update->getSuccessfulDevicesCount(),
                'failed_devices' => $update->getFailedDevicesCount(),
                'success_rate' => $update->getSuccessRate(),
                'duration_minutes' => $update->getDurationMinutes(),
                'started_at' => $update->started_at,
                'completed_at' => $update->completed_at,
                'error_message' => $update->error_message,
                'created_at' => $update->created_at
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $updates
        ]);
    }

    /**
     * Receber feedback de dispositivo (webhook/callback)
     * POST /api/mqtt/ota-updates/{id}/device-feedback
     */
    public function deviceFeedback(Request $request, int $otaLogId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'status' => 'required|in:success,failed,in_progress',
            'message' => 'nullable|string',
            'firmware_version' => 'nullable|string',
            'error_code' => 'nullable|string',
            'progress_percent' => 'nullable|integer|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->otaService->processDeviceFeedback(
            $otaLogId,
            $request->device_id,
            $request->all()
        );

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Feedback processado com sucesso' : 'Erro ao processar feedback'
        ]);
    }

    /**
     * Cancelar update OTA
     * POST /api/mqtt/ota-updates/{id}/cancel
     */
    public function cancelUpdate(int $otaLogId): JsonResponse
    {
        $otaLog = OtaUpdateLog::find($otaLogId);
        
        if (!$otaLog) {
            return response()->json([
                'success' => false,
                'message' => 'Update OTA não encontrado'
            ], 404);
        }

        if (!$otaLog->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Este update não pode ser cancelado (status: ' . $otaLog->status . ')'
            ], 400);
        }

        $otaLog->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'error_message' => 'Cancelado pelo usuário'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Update OTA cancelado com sucesso'
        ]);
    }

    /**
     * Buscar estatísticas gerais de OTA
     * GET /api/mqtt/ota-stats
     */
    public function getStats(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 30);
        
        $baseQuery = OtaUpdateLog::where('created_at', '>=', now()->subDays($days));
        
        $stats = [
            'total_updates' => $baseQuery->count(),
            'successful_updates' => $baseQuery->where('status', 'completed')->count(),
            'failed_updates' => $baseQuery->where('status', 'failed')->count(),
            'active_updates' => OtaUpdateLog::whereIn('status', ['initiated', 'in_progress'])->count(),
            'total_devices_updated' => $baseQuery->where('status', 'completed')->sum('devices_count'),
            'avg_success_rate' => $baseQuery->where('status', 'completed')->avg('devices_count') ?: 0,
            'device_types_with_updates' => $baseQuery->distinct('device_type_id')->count('device_type_id')
        ];

        // Updates por tipo de dispositivo
        $updatesByType = $baseQuery
            ->with('deviceType')
            ->selectRaw('device_type_id, COUNT(*) as count, AVG(devices_count) as avg_devices')
            ->groupBy('device_type_id')
            ->get()
            ->map(function ($item) {
                return [
                    'device_type' => $item->deviceType->name,
                    'updates_count' => $item->count,
                    'avg_devices' => round($item->avg_devices, 2)
                ];
            });

        // Updates por status
        $updatesByStatus = $baseQuery
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'success' => true,
            'period_days' => $days,
            'stats' => $stats,
            'updates_by_type' => $updatesByType,
            'updates_by_status' => $updatesByStatus
        ]);
    }

    /**
     * Buscar logs detalhados de um update específico
     * GET /api/mqtt/ota-updates/{id}/logs
     */
    public function getUpdateLogs(int $otaLogId): JsonResponse
    {
        $otaLog = OtaUpdateLog::with('deviceType')->find($otaLogId);
        
        if (!$otaLog) {
            return response()->json([
                'success' => false,
                'message' => 'Update OTA não encontrado'
            ], 404);
        }

        $deviceResults = $otaLog->device_results ?? [];
        
        // Organizar resultados por status
        $resultsByStatus = collect($deviceResults)->groupBy('status');
        
        return response()->json([
            'success' => true,
            'data' => [
                'update_info' => [
                    'id' => $otaLog->id,
                    'device_type' => $otaLog->deviceType->name,
                    'firmware_version' => $otaLog->firmware_version,
                    'status' => $otaLog->status,
                    'devices_count' => $otaLog->devices_count,
                    'firmware_url' => $otaLog->firmware_url,
                    'firmware_size_mb' => round(($otaLog->firmware_size_bytes ?? 0) / 1024 / 1024, 2),
                    'started_at' => $otaLog->started_at,
                    'completed_at' => $otaLog->completed_at,
                    'duration_minutes' => $otaLog->getDurationMinutes(),
                    'error_message' => $otaLog->error_message
                ],
                'device_results' => $deviceResults,
                'results_summary' => [
                    'successful' => $resultsByStatus->get('success', collect())->count(),
                    'failed' => $resultsByStatus->get('failed', collect())->count(),
                    'in_progress' => $resultsByStatus->get('in_progress', collect())->count(),
                    'success_rate' => $otaLog->getSuccessRate()
                ],
                'metadata' => $otaLog->metadata
            ]
        ]);
    }
}
