@extends('layouts.app')

@section('title', 'Sobre')

@section('content')
<div class="home-container">
    <div class="header">
        <h1>ℹ️ Sobre o Sistema</h1>
        <p>Informações sobre o IoT Configuration System</p>
    </div>

    <div class="device-card">
        <h2>🔌 IoT Configuration System</h2>
        <p class="mb-4">Sistema completo para configuração e gerenciamento de dispositivos IoT.</p>
        
        <div class="space-y-4">
            <div>
                <h3 class="text-lg font-semibold mb-2">✨ Funcionalidades</h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Configuração automática de dispositivos IoT</li>
                    <li>Escaneamento de redes WiFi</li>
                    <li>Criação automática de tópicos MQTT</li>
                    <li>Geração de MAC addresses únicos</li>
                    <li>Interface responsiva e intuitiva</li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">🛠️ Tecnologias</h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Laravel 12 - Framework PHP</li>
                    <li>Blade Templates - Sistema de templates</li>
                    <li>MySQL/SQLite - Banco de dados</li>
                    <li>MQTT - Protocolo de comunicação</li>
                    <li>CSS3 - Estilização moderna</li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">📱 Como Usar</h3>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Escaneie as redes WiFi disponíveis</li>
                    <li>Selecione a rede desejada</li>
                    <li>Configure os dados do dispositivo</li>
                    <li>Salve a configuração</li>
                    <li>O sistema criará automaticamente o tópico MQTT</li>
                </ol>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">🔧 Suporte</h3>
                <p>Para suporte técnico, entre em contato com a equipe de desenvolvimento.</p>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="configure-btn">
                🏠 Voltar ao Início
            </a>
        </div>
    </div>
</div>

<style>
.space-y-4 > * + * {
    margin-top: 1rem;
}

.space-y-1 > * + * {
    margin-top: 0.25rem;
}

.text-lg {
    font-size: 1.125rem;
    line-height: 1.75rem;
}

.font-semibold {
    font-weight: 600;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mt-6 {
    margin-top: 1.5rem;
}

.list-disc {
    list-style-type: disc;
}

.list-decimal {
    list-style-type: decimal;
}

.list-inside {
    list-style-position: inside;
}

.text-center {
    text-align: center;
}
</style>
@endsection

