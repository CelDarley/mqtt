<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function index()
    {
        // Sempre mostrar o dashboard normal - não verificar conexão com dispositivo IoT aqui
        // A verificação só será feita quando tentar adicionar um novo dispositivo
        
        return view('home', [
            'showDashboard' => true,
            'message' => 'Sistema IoT Config - Gerencie seus dispositivos e tópicos MQTT'
        ]);
    }
    
    public function checkDeviceConnection()
    {
        // Método separado para verificar conexão com dispositivo IoT
        // Só é chamado quando o usuário quer adicionar um novo dispositivo
        try {
            $response = Http::timeout(3)->get('http://192.168.4.1:5000/api/status');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getDeviceStatus()
    {
        try {
            $response = Http::timeout(5)->get('http://192.168.4.1:5000/api/status');
            
            if ($response->successful()) {
                return response()->json([
                    'connected' => true,
                    'data' => $response->json()
                ]);
            }
            
            return response()->json(['connected' => false]);
        } catch (\Exception $e) {
            return response()->json(['connected' => false, 'error' => $e->getMessage()]);
        }
    }
}

