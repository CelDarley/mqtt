@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="home-container">
    <div class="header">
        <h1>🔌 IoT Configuration</h1>
        <p>{{ $message }}</p>
    </div>

    @if($errors->has('device_connection'))
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-lg font-medium">{{ $errors->first('device_connection') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('success_message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-lg font-medium">{{ session('success_message') }}</p>
                    @if(session('topic_name'))
                        <div class="mt-2 text-sm">
                            <strong>Tópico:</strong> <code class="bg-green-200 px-2 py-1 rounded">{{ session('topic_name') }}</code>
                        </div>
                    @endif
                    @if(session('device_name'))
                        <div class="mt-1 text-sm">
                            <strong>Dispositivo:</strong> {{ session('device_name') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Dashboard Principal -->
    <div class="dashboard-section">
        <div class="dashboard-card">
            <h2>📊 Tópicos MQTT</h2>
            <p>Gerencie os tópicos MQTT dos seus dispositivos IoT</p>
            
            <div class="topics-list" id="topicsList">
                <div class="loading" id="loadingTopics">
                    <p>⏳ Carregando tópicos...</p>
                </div>
                <div class="no-topics" id="noTopics" style="display: none;">
                    <p>📭 Nenhum tópico encontrado</p>
                    <p class="text-gray-400">Adicione seu primeiro dispositivo para começar!</p>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <h2>➕ Adicionar Dispositivo</h2>
            <p>Configure um novo dispositivo IoT</p>
            
            <div class="add-device-section">
                <div class="instruction-box">
                    <h3>📋 Como adicionar um dispositivo:</h3>
                    <ol>
                        <li>🔌 Ligue o dispositivo IoT</li>
                        <li>📱 Conecte-se à rede WiFi <strong>IOT-Zontec</strong></li>
                        <li>🖱️ Clique no botão "Adicionar Dispositivo" abaixo</li>
                        <li>📝 Preencha as informações do dispositivo</li>
                    </ol>
                </div>
                
                <div class="text-center mt-6">
                    <a href="{{ route('device.add') }}" class="add-device-button">
                        📱 Adicionar Dispositivo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Carregar tópicos MQTT
async function loadTopics() {
    try {
        const response = await fetch('http://localhost:8000/api/mqtt/topics');
        const data = await response.json();
        
        const loadingEl = document.getElementById('loadingTopics');
        const noTopicsEl = document.getElementById('noTopics');
        const topicsListEl = document.getElementById('topicsList');
        
        loadingEl.style.display = 'none';
        
        if (data.data && data.data.length > 0) {
            const topicsHtml = data.data.map(topic => `
                <div class="topic-item">
                    <h4>${topic.name}</h4>
                    <p>${topic.description || 'Sem descrição'}</p>
                    <small>Criado: ${new Date(topic.created_at).toLocaleDateString('pt-BR')}</small>
                </div>
            `).join('');
            
            topicsListEl.innerHTML = topicsHtml;
        } else {
            noTopicsEl.style.display = 'block';
        }
    } catch (error) {
        console.error('Erro ao carregar tópicos:', error);
        document.getElementById('loadingTopics').innerHTML = '<p>❌ Erro ao carregar tópicos</p>';
    }
}

// Carregar tópicos ao inicializar
document.addEventListener('DOMContentLoaded', function() {
    loadTopics();
});
</script>

<style>
.dashboard-section {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

@media (min-width: 768px) {
    .dashboard-section {
        grid-template-columns: 1fr 1fr;
    }
}

.dashboard-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 2rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.dashboard-card h2 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #ffffff;
}

.dashboard-card p {
    color: #e0e7ff;
    margin-bottom: 1.5rem;
}

.topics-list {
    min-height: 200px;
}

.topic-item {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    border-left: 3px solid #3b82f6;
}

.topic-item h4 {
    color: #ffffff;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.topic-item p {
    color: #d1d5db;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.topic-item small {
    color: #9ca3af;
    font-size: 0.8rem;
}

.instruction-box {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.instruction-box h3 {
    color: #ffffff;
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.instruction-box ol {
    color: #e0e7ff;
    padding-left: 1.5rem;
}

.instruction-box li {
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.add-device-button {
    display: inline-block;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 1rem 2rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: transform 0.2s;
    border: none;
    cursor: pointer;
}

.add-device-button:hover {
    transform: translateY(-2px);
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}

.loading, .no-topics {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
}

.no-topics p {
    margin-bottom: 0.5rem;
}

.text-gray-400 {
    color: #9ca3af;
    font-size: 0.9rem;
}

.mt-6 {
    margin-top: 1.5rem;
}

/* Estilos existentes mantidos */
.flex {
    display: flex;
}

.items-center {
    align-items: center;
}

.flex-shrink-0 {
    flex-shrink: 0;
}

.ml-3 {
    margin-left: 0.75rem;
}

.mb-6 {
    margin-bottom: 1.5rem;
}

.px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
}

.py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.rounded-lg {
    border-radius: 0.5rem;
}

.border-l-4 {
    border-left-width: 4px;
}

.border-yellow-500 {
    border-color: #eab308;
}

.bg-yellow-100 {
    background-color: #fef3c7;
}

.text-yellow-700 {
    color: #a16207;
}

.text-lg {
    font-size: 1.125rem;
    line-height: 1.75rem;
}

.font-medium {
    font-weight: 500;
}
</style>
@endsection

