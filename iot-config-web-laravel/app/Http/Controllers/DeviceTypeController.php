<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeviceTypeController extends Controller
{
    private $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = config('app.api_base_url', 'http://localhost:8000/api');
    }

    /**
     * Listar todos os tipos de dispositivo
     */
    public function index(Request $request)
    {
        try {
            $response = Http::get($this->apiBaseUrl . '/mqtt/device-types', [
                'active_only' => $request->get('active_only'),
                'search' => $request->get('search'),
            ]);

            if ($response->successful()) {
                $deviceTypes = $response->json()['data'] ?? [];

                // Buscar estatísticas
                $statsResponse = Http::get($this->apiBaseUrl . '/mqtt/device-types/stats');
                $stats = $statsResponse->successful() ? $statsResponse->json()['data'] ?? [] : [];

                return view('device-types.index', compact('deviceTypes', 'stats'));
            }

            return view('device-types.index')->with('error', 'Erro ao carregar tipos de dispositivo');
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de dispositivo: ' . $e->getMessage());
            return view('device-types.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        return view('device-types.create');
    }

    /**
     * Armazenar novo tipo de dispositivo
     */
    public function store(Request $request)
    {
        try {
            $specifications = [];
            if ($request->specifications) {
                $specifications = json_decode($request->specifications, true);
                
                // Verificar se houve erro na decodificação JSON
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withInput()->with('error', 'Especificações JSON inválidas: ' . json_last_error_msg());
                }
                
                $specifications = $specifications ?? [];
            }

            $response = Http::post($this->apiBaseUrl . '/mqtt/device-types', [
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
                'specifications' => $specifications,
                'is_active' => $request->has('is_active'),
            ]);

            if ($response->successful()) {
                return redirect()->route('device-types.index')->with('success', 'Tipo de dispositivo criado com sucesso!');
            }

            $responseData = $response->json();
            $errorMessage = $responseData['message'] ?? 'Erro ao criar tipo de dispositivo';
            
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
            Log::error('Erro ao criar tipo de dispositivo: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Mostrar detalhes do tipo de dispositivo
     */
    public function show($id)
    {
        try {
            $response = Http::get($this->apiBaseUrl . "/mqtt/device-types/{$id}");

            if ($response->successful()) {
                $deviceType = $response->json()['data'];
                return view('device-types.show', compact('deviceType'));
            }

            return redirect()->route('device-types.index')->with('error', 'Tipo de dispositivo não encontrado');
        } catch (\Exception $e) {
            Log::error('Erro ao mostrar tipo de dispositivo: ' . $e->getMessage());
            return redirect()->route('device-types.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Mostrar formulário de edição
     */
    public function edit($id)
    {
        try {
            $response = Http::get($this->apiBaseUrl . "/mqtt/device-types/{$id}");
            
            if ($response->successful()) {
                $deviceType = $response->json()['data'];
                return view('device-types.edit', compact('deviceType'));
            }

            return redirect()->route('device-types.index')->with('error', 'Tipo de dispositivo não encontrado');
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return redirect()->route('device-types.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Atualizar tipo de dispositivo
     */
    public function update(Request $request, $id)
    {
        try {
            $specifications = [];
            if ($request->specifications) {
                $specifications = json_decode($request->specifications, true) ?? [];
            }

            $response = Http::put($this->apiBaseUrl . "/mqtt/device-types/{$id}", [
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
                'specifications' => $specifications,
                'is_active' => $request->has('is_active'),
            ]);

            if ($response->successful()) {
                return redirect()->route('device-types.index')->with('success', 'Tipo de dispositivo atualizado com sucesso!');
            }

            $errorMessage = $response->json()['message'] ?? 'Erro ao atualizar tipo de dispositivo';
            return back()->withInput()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar tipo de dispositivo: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Deletar tipo de dispositivo
     */
    public function destroy($id)
    {
        try {
            $response = Http::delete($this->apiBaseUrl . "/mqtt/device-types/{$id}");

            if ($response->successful()) {
                return redirect()->route('device-types.index')->with('success', 'Tipo de dispositivo deletado com sucesso!');
            }

            $errorMessage = $response->json()['message'] ?? 'Erro ao deletar tipo de dispositivo';
            return redirect()->route('device-types.index')->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar tipo de dispositivo: ' . $e->getMessage());
            return redirect()->route('device-types.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Alternar status do tipo de dispositivo
     */
    public function toggleStatus($id)
    {
        try {
            $response = Http::patch($this->apiBaseUrl . "/mqtt/device-types/{$id}/toggle-status");

            if ($response->successful()) {
                return redirect()->route('device-types.index')->with('success', 'Status atualizado com sucesso!');
            }

            $errorMessage = $response->json()['message'] ?? 'Erro ao atualizar status';
            return redirect()->route('device-types.index')->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao alternar status: ' . $e->getMessage());
            return redirect()->route('device-types.index')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Iniciar atualização OTA para um tipo de dispositivo
     */
    public function otaUpdate(Request $request, $id)
    {
        try {
            $response = Http::post($this->apiBaseUrl . "/mqtt/device-types/{$id}/ota-update", [
                'force_update' => $request->get('force_update', false),
                'user_id' => $request->get('user_id', 1), // TODO: Pegar do usuário logado
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('Erro ao iniciar OTA update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro de conexão com a API'
            ], 500);
        }
    }

    /**
     * Buscar status de um update OTA específico
     */
    public function otaStatus($id)
    {
        try {
            $response = Http::get($this->apiBaseUrl . "/mqtt/ota-updates/{$id}");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('Erro ao buscar status OTA: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro de conexão com a API'
            ], 500);
        }
    }

    /**
     * Listar updates OTA
     */
    public function otaUpdates(Request $request)
    {
        try {
            $queryParams = $request->only(['device_type_id', 'status', 'days', 'per_page']);
            $response = Http::get($this->apiBaseUrl . "/mqtt/ota-updates", $queryParams);
            
            if ($response->successful()) {
                $updates = $response->json()['data'] ?? [];
                return view('device-types.ota-updates', compact('updates'));
            }

            return view('device-types.ota-updates')->with('error', 'Erro ao carregar updates OTA');
        } catch (\Exception $e) {
            Log::error('Erro ao listar updates OTA: ' . $e->getMessage());
            return view('device-types.ota-updates')->with('error', 'Erro de conexão com a API');
        }
    }

    /**
     * Buscar informações de firmware disponível
     */
    public function firmwareInfo($id)
    {
        try {
            $response = Http::get($this->apiBaseUrl . "/mqtt/device-types/{$id}/firmware-info");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('Erro ao buscar firmware info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro de conexão com a API'
            ], 500);
        }
    }
}
