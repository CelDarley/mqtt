@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <h1>👑 Painel Administrativo</h1>
        <p>Bem-vindo ao sistema de gerenciamento IoT</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Card de Usuários -->
        <div class="dashboard-card">
            <h2>👥 Gerenciar Usuários</h2>
            <p>Visualize e gerencie todos os usuários do sistema, incluindo administradores e usuários comuns.</p>
            <div class="flex gap-3">
                <a href="{{ route('users.index') }}" class="card-btn primary">
                    👥 Usuários
                </a>
                <button onclick="showUserStats()" class="card-btn secondary">
                    📊 Estatísticas
                </button>
            </div>
        </div>

        <!-- Card de Tópicos MQTT -->
        <div class="dashboard-card">
            <h2>📡 Tópicos MQTT</h2>
            <p>Monitore e gerencie todos os tópicos MQTT ativos no sistema.</p>
            <div class="flex gap-3">
                <a href="{{ route('topics.index') }}" class="card-btn primary">
                    📡 Tópicos
                </a>
                <button onclick="showTopicStats()" class="card-btn secondary">
                    📊 Monitorar
                </button>
            </div>
        </div>

        <!-- Card de Dispositivos -->
        <div class="dashboard-card">
            <h2>📱 Dispositivos IoT</h2>
            <p>Configure e monitore dispositivos IoT conectados ao sistema.</p>
            <div class="flex gap-3">
                <a href="{{ route('devices.index') }}" class="card-btn primary">
                    📱 Dispositivos
                </a>
                <button onclick="showDeviceStats()" class="card-btn secondary">
                    📊 Status
                </button>
            </div>
        </div>
    </div>

    <!-- Estatísticas Rápidas -->
    <div class="quick-stats">
        <h2>📊 Estatísticas Rápidas</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="stat-item">
                <div class="stat-value">{{ $stats['totalUsers'] ?? 0 }}</div>
                <div class="stat-label">Total de Usuários</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['activeUsers'] ?? 0 }}</div>
                <div class="stat-label">Usuários Ativos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['totalTopics'] ?? 0 }}</div>
                <div class="stat-label">Tópicos MQTT</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['activeDevices'] ?? 0 }}</div>
                <div class="stat-label">Dispositivos Ativos</div>
            </div>
        </div>
    </div>

    <!-- Ações Recentes -->
    <div class="recent-actions">
        <h2>🕒 Ações Recentes</h2>
        <div class="space-y-3">
            <div class="action-item">
                <div class="flex items-center gap-3">
                    <div class="action-icon">👤</div>
                    <div class="flex-1">
                        <div class="action-description">Novo usuário cadastrado</div>
                        <div class="action-time">Há 5 minutos</div>
                    </div>
                    <div class="action-user">admin</div>
                </div>
            </div>
            <div class="action-item">
                <div class="flex items-center gap-3">
                    <div class="action-icon">📡</div>
                    <div class="flex-1">
                        <div class="action-description">Tópico MQTT criado</div>
                        <div class="action-time">Há 15 minutos</div>
                    </div>
                    <div class="action-user">darley</div>
                </div>
            </div>
            <div class="action-item">
                <div class="flex items-center gap-3">
                    <div class="action-icon">📱</div>
                    <div class="flex-1">
                        <div class="action-description">Dispositivo configurado</div>
                        <div class="action-time">Há 1 hora</div>
                    </div>
                    <div class="action-user">usuario1</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quick-stats {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.quick-stats h2 {
    margin: 0 0 20px 0;
    color: var(--color-primary-dark);
    font-size: 1.5rem;
    font-weight: 600;
}

.stat-item {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-primary-dark);
    margin-bottom: 8px;
}

.stat-label {
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
}

.recent-actions {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.recent-actions h2 {
    margin: 0 0 20px 0;
    color: var(--color-primary-dark);
    font-size: 1.5rem;
    font-weight: 600;
}

.action-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: background-color 0.2s ease;
}

.action-item:hover {
    background: #e9ecef;
}

.action-icon {
    font-size: 1.5rem;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-primary-lightest);
    border-radius: 50%;
}

.action-description {
    font-weight: 600;
    color: var(--color-primary-dark);
    margin-bottom: 4px;
}

.action-time {
    font-size: 12px;
    color: #6c757d;
}

.action-user {
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 12px;
}
</style>

<script>
function showUserStats() {
    alert('Estatísticas de usuários serão implementadas em breve!');
}

function showTopicStats() {
    alert('Estatísticas de tópicos MQTT serão implementadas em breve!');
}

function showDeviceStats() {
    alert('Estatísticas de dispositivos serão implementadas em breve!');
}
</script>
@endsection

