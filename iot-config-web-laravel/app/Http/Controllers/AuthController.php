<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        \Log::info('Tentativa de login para: ' . $request->email);

        // Tentar autenticar via API MQTT
        try {
            $response = Http::timeout(5)->post('http://localhost:8000/api/auth/login', [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            \Log::info('Resposta da API MQTT: ' . $response->status());

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    $userData = $data['data']['user'];
                    
                    // Criar usuário local se não existir
                    $user = \App\Models\User::updateOrCreate(
                        ['email' => $userData['email']],
                        [
                            'name' => $userData['name'],
                            'email' => $userData['email'],
                            'tipo' => $userData['tipo'],
                            'id_comp' => $userData['id_comp'] ?? null,
                        ]
                    );

                    // Fazer login local
                    Auth::login($user);
                    
                    // Armazenar token da API
                    session(['api_token' => $data['data']['token']]);
                    
                    \Log::info('Login via API MQTT bem-sucedido para: ' . $request->email);
                    return redirect()->intended(route('dashboard'));
                }
            }
        } catch (\Exception $e) {
            \Log::warning('API MQTT não disponível, tentando autenticação local: ' . $e->getMessage());
        }

        // Fallback: tentar autenticação local
        \Log::info('Tentando autenticação local para: ' . $request->email);
        
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            \Log::info('Login local bem-sucedido para: ' . $request->email);
            return redirect()->intended(route('dashboard'));
        }

        \Log::warning('Falha na autenticação para: ' . $request->email);

        throw ValidationException::withMessages([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}

