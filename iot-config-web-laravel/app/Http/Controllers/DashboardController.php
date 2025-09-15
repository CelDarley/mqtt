<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalUsers' => 0,
            'activeUsers' => 0,
            'totalTopics' => 0,
            'activeDevices' => 0,
        ];

        try {
            // Buscar estatísticas da API MQTT
            $usersResponse = Http::get('http://localhost:8000/api/users/stats');
            if ($usersResponse->successful()) {
                $userStats = $usersResponse->json();
                $stats['totalUsers'] = $userStats['data']['total_users'] ?? 0;
                $stats['activeUsers'] = $userStats['data']['users_with_company'] ?? 0;
            }

            $topicsResponse = Http::get('http://localhost:8000/api/mqtt/topics');
            if ($topicsResponse->successful()) {
                $topicsData = $topicsResponse->json();
                $stats['totalTopics'] = count($topicsData['data'] ?? []);
            }
        } catch (\Exception $e) {
            // Usar dados padrão se a API não estiver disponível
            $stats = [
                'totalUsers' => 1,
                'activeUsers' => 1,
                'totalTopics' => 0,
                'activeDevices' => 0,
            ];
        }

        return view('dashboard', compact('stats'));
    }
}

