<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PendingDeviceController extends Controller
{
    private $backendUrl = 'http://10.102.0.101:8000/api';

    public function index()
    {
        try {
            // Buscar dispositivos pendentes da API
            $response = Http::get("{$this->backendUrl}/devices/pending");
            
            if ($response->successful()) {
                $data = $response->json();
                return view('pending-devices.index', [
                    'devices' => $data['data'] ?? [],
                    'stats' => $data['stats'] ?? []
                ]);
            }
            
            return view('pending-devices.index', [
                'devices' => [],
                'stats' => [],
                'error' => 'Erro ao carregar dispositivos pendentes'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar pending devices: ' . $e->getMessage());
            
            return view('pending-devices.index', [
                'devices' => [],
                'stats' => [],
                'error' => 'Erro de conexão com o backend'
            ]);
        }
    }

    public function show($id)
    {
        try {
            $response = Http::get("{$this->backendUrl}/devices/pending/{$id}");
            
            if ($response->successful()) {
                $data = $response->json();
                return view('pending-devices.show', [
                    'device' => $data['data']
                ]);
            }
            
            return redirect()->route('pending-devices.index')
                           ->with('error', 'Dispositivo não encontrado');
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dispositivo: ' . $e->getMessage());
            
            return redirect()->route('pending-devices.index')
                           ->with('error', 'Erro ao carregar dispositivo');
        }
    }

    public function activate($id)
    {
        try {
            // Buscar dados do dispositivo
            $response = Http::get("{$this->backendUrl}/devices/pending/{$id}");
            
            if (!$response->successful()) {
                return redirect()->route('pending-devices.index')
                               ->with('error', 'Dispositivo não encontrado');
            }
            
            $data = $response->json();
            return view('pending-devices.activate', [
                'device' => $data['data']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar página de ativação: ' . $e->getMessage());
            
            return redirect()->route('pending-devices.index')
                           ->with('error', 'Erro ao carregar página de ativação');
        }
    }

    public function processActivation(Request $request, $id)
    {
        try {
            $request->validate([
                'device_type' => 'required|string|in:sensor,atuador,controlador,monitor',
                'department' => 'required|string|max:50'
            ]);

            // Simular token de autenticação (em produção usar JWT real)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer fake-token',
                'Content-Type' => 'application/json'
            ])->post("{$this->backendUrl}/devices/pending/{$id}/activate", [
                'device_type' => $request->device_type,
                'department' => $request->department
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return redirect()->route('pending-devices.index')
                               ->with('success', $data['message'] ?? 'Dispositivo ativado com sucesso!');
            } else {
                $error = $response->json()['message'] ?? 'Erro ao ativar dispositivo';
                return back()->with('error', $error)->withInput();
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao ativar dispositivo: ' . $e->getMessage());
            
            return back()->with('error', 'Erro interno do servidor')->withInput();
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer fake-token'
            ])->post("{$this->backendUrl}/devices/pending/{$id}/reject");

            if ($response->successful()) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'message' => 'Dispositivo rejeitado com sucesso']);
                }
                return redirect()->route('pending-devices.index')
                               ->with('success', 'Dispositivo rejeitado com sucesso');
            } else {
                $error = $response->json()['message'] ?? 'Erro ao rejeitar dispositivo';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $error], 400);
                }
                return back()->with('error', $error);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao rejeitar dispositivo: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
            }
            return back()->with('error', 'Erro interno do servidor');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer fake-token'
            ])->delete("{$this->backendUrl}/devices/pending/{$id}");

            if ($response->successful()) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'message' => 'Dispositivo excluído com sucesso']);
                }
                return redirect()->route('pending-devices.index')
                               ->with('success', 'Dispositivo excluído com sucesso');
            } else {
                $error = $response->json()['message'] ?? 'Erro ao excluir dispositivo';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $error], 400);
                }
                return back()->with('error', $error);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir dispositivo: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
            }
            return back()->with('error', 'Erro interno do servidor');
        }
    }

    // AJAX Methods
    public function stats()
    {
        try {
            $response = Http::get("{$this->backendUrl}/devices/pending/stats");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro de conexão'
            ], 500);
        }
    }

    public function findByMac(Request $request)
    {
        try {
            $request->validate([
                'mac_address' => 'required|string'
            ]);

            $response = Http::get("{$this->backendUrl}/devices/pending/find-by-mac", [
                'mac_address' => $request->mac_address
            ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'success' => false,
                'message' => $response->json()['message'] ?? 'Dispositivo não encontrado'
            ], $response->status());
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'MAC address inválido',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar por MAC: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro de conexão'
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $response = Http::get("{$this->backendUrl}/devices/pending");
            
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'devices' => $data['data'] ?? [],
                    'stats' => $data['stats'] ?? []
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar lista'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar pending devices: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro de conexão'
            ], 500);
        }
    }
}
