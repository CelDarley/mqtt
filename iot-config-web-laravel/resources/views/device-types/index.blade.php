@extends('layouts.app')

@section('title', 'Tipos de Dispositivo')

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <div class="page-header-content">
            <h1 style="color: #eeeeee;">📱 Tipos de Dispositivo</h1>
            <p>Gerencie os tipos de dispositivos IoT do sistema</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('device-types.create') }}" class="btn btn-primary">
                ➕ Novo Tipo
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Estatísticas -->
    @if(!empty($stats))
    <div class="quick-stats">
        <h2>📊 Estatísticas</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="stat-item">
                <div class="stat-value">{{ $stats['total_types'] ?? 0 }}</div>
                <div class="stat-label">Total de Tipos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['active_types'] ?? 0 }}</div>
                <div class="stat-label">Tipos Ativos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['inactive_types'] ?? 0 }}</div>
                <div class="stat-label">Tipos Inativos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['types_with_topics'] ?? 0 }}</div>
                <div class="stat-label">Com Tópicos</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros -->
    <div class="dashboard-card">
        <h2>🔍 Filtros</h2>
        <form method="GET" action="{{ route('device-types.index') }}" class="filter-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="search">Buscar:</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           class="form-control" 
                           value="{{ request('search') }}" 
                           placeholder="Nome do tipo...">
                </div>
                <div class="form-group">
                    <label for="active_only">Status:</label>
                    <select name="active_only" id="active_only" class="form-control">
                        <option value="">Todos</option>
                        <option value="1" {{ request('active_only') == '1' ? 'selected' : '' }}>Apenas Ativos</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-secondary">Filtrar</button>
                    <a href="{{ route('device-types.index') }}" class="btn btn-outline">Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Lista de Tipos de Dispositivo -->
    <div class="dashboard-card">
        <h2>📋 Lista de Tipos de Dispositivo</h2>
        
        @if(count($deviceTypes ?? []) > 0)
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ícone</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deviceTypes as $deviceType)
                            <tr>
                                <td>
                                    <span class="device-icon">
                                        {{ $deviceType['icon'] ?? '📱' }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $deviceType['name'] }}</strong>
                                </td>
                                <td>
                                    <span class="description">
                                        {{ Str::limit($deviceType['description'] ?? 'N/A', 50) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $deviceType['is_active'] ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $deviceType['is_active'] ? '✅ Ativo' : '❌ Inativo' }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($deviceType['created_at'])->format('d/m/Y H:i') }}
                                </td>
                                <td class="actions">
                                    <a href="{{ route('device-types.show', $deviceType['id']) }}" class="btn-action btn-view" title="Visualizar">
                                        👁️
                                    </a>
                                    <button onclick="triggerOtaUpdate({{ $deviceType['id'] }})" 
                                            class="btn-action btn-ota" 
                                            title="Atualizar Firmware OTA"
                                            {{ $deviceType['is_active'] ? '' : 'disabled' }}>
                                        🔄
                                    </button>
                                    <a href="{{ route('device-types.edit', $deviceType['id']) }}" class="btn-action btn-edit" title="Editar">
                                        ✏️
                                    </a>
                                    <form method="POST" action="{{ route('device-types.toggle-status', $deviceType['id']) }}" 
                                          style="display: inline-block;"
                                          onsubmit="return confirm('Tem certeza que deseja {{ $deviceType['is_active'] ? 'desativar' : 'ativar' }} este tipo?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-action {{ $deviceType['is_active'] ? 'btn-warning' : 'btn-success' }}" 
                                                title="{{ $deviceType['is_active'] ? 'Desativar' : 'Ativar' }}">
                                            {{ $deviceType['is_active'] ? '⏸️' : '▶️' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('device-types.destroy', $deviceType['id']) }}" 
                                          style="display: inline-block;"
                                          onsubmit="return confirm('Tem certeza que deseja deletar este tipo de dispositivo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" title="Deletar">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <p>📭 Nenhum tipo de dispositivo encontrado.</p>
                <a href="{{ route('device-types.create') }}" class="btn btn-primary">
                    ➕ Criar Primeiro Tipo
                </a>
            </div>
        @endif
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-header-content h1 {
    margin: 0;
    color: #2563eb;
}

.page-header-content p {
    margin: 0.5rem 0 0 0;
    color: #6b7280;
}

.quick-stats {
    margin-bottom: 2rem;
}

.quick-stats .grid {
    display: grid;
    gap: 1rem;
}

@media (min-width: 768px) {
    .grid.md\\:grid-cols-4 {
        grid-template-columns: repeat(4, 1fr);
    }
}

.stat-item {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    text-align: center;
    border: 1px solid #e5e7eb;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2563eb;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.5rem;
}

.filter-form {
    margin: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.table-container {
    overflow-x: auto;
    background-color: #ffffff;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    background-color: #ffffff;
}

/* Força a cor do texto em todas as células */
.data-table * {
    color: #374151 !important;
}

.data-table strong,
.data-table td strong {
    color: #111827 !important;
    font-weight: 600;
}

/* Exceções para badges, botões e descrições que devem manter suas cores */
.data-table .badge,
.data-table .btn-action {
    color: inherit !important;
}

.data-table .description {
    color: #6b7280 !important;
}

.data-table th,
.data-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
}

.data-table th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.data-table td {
    background-color: #ffffff;
    color: #374151;
}

.data-table td strong {
    color: #111827;
}

.data-table td span {
    color: #374151;
}

.device-icon {
    font-size: 1.5rem;
    display: inline-block;
}

.description {
    font-size: 0.875rem;
}

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    padding: 0.25rem 0.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.btn-view { background-color: #3b82f6; color: white; }
.btn-edit { background-color: #f59e0b; color: white; }
.btn-delete { background-color: #ef4444; color: white; }
.btn-warning { background-color: #f59e0b; color: white; }
.btn-success { background-color: #10b981; color: white; }

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-active { 
    background-color: #dcfce7; 
    color: #166534; 
}

.badge-inactive { 
    background-color: #fde2e8; 
    color: #991b1b; 
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background-color: #fde2e8;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    cursor: pointer;
    border: none;
    font-size: 1rem;
}

.btn-primary {
    background-color: #2563eb;
    color: white;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-outline {
    background-color: white;
    color: #374151;
    border: 1px solid #d1d5db;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-control {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 1rem;
}

/* Botão OTA */
.btn-ota {
    background-color: #16a34a !important;
    color: white !important;
    transition: all 0.2s ease;
}

.btn-ota:hover:not(:disabled) {
    background-color: #15803d !important;
    transform: scale(1.05);
}

.btn-ota:disabled {
    background-color: #9ca3af !important;
    color: #6b7280 !important;
    cursor: not-allowed;
    opacity: 0.5;
}

/* Modal OTA */
.ota-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    animation: fadeIn 0.3s ease;
}

.ota-modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 0.5rem;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.ota-modal.show {
    display: block;
}

.ota-progress {
    width: 100%;
    height: 8px;
    background-color: #f3f4f6;
    border-radius: 4px;
    margin: 1rem 0;
    overflow: hidden;
}

.ota-progress-bar {
    height: 100%;
    background-color: #16a34a;
    transition: width 0.3s ease;
    border-radius: 4px;
}

.ota-log {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 1rem;
    max-height: 200px;
    overflow-y: auto;
    font-family: monospace;
    font-size: 0.875rem;
    margin: 1rem 0;
}

.ota-status {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    margin: 0.5rem 0;
    font-weight: 600;
}

.ota-status.success {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.ota-status.error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.ota-status.warning {
    background-color: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.ota-status.info {
    background-color: #dbeafe;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.pulse {
    animation: pulse 2s infinite;
}
</style>

<!-- Modal OTA -->
<div id="otaModal" class="ota-modal">
    <div class="ota-modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; color: #1f2937;">🔄 Atualização de Firmware OTA</h3>
            <button onclick="closeOtaModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <div id="otaContent">
            <!-- Conteúdo será preenchido dinamicamente -->
        </div>
        
        <div id="otaActions" style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
            <button onclick="closeOtaModal()" class="btn btn-secondary">Fechar</button>
        </div>
    </div>
</div>

<script>
let currentOtaId = null;
let otaPollingInterval = null;

function triggerOtaUpdate(deviceTypeId) {
    if (!confirm('🔄 Tem certeza que deseja iniciar a atualização de firmware para todos os dispositivos deste tipo?\n\nEsta ação enviará comandos OTA via MQTT para todos os dispositivos ativos.')) {
        return;
    }
    
    // Mostrar modal de progresso
    showOtaModal('Iniciando atualização OTA...', 'info');
    
    // Fazer requisição para iniciar OTA
    fetch(`/device-types/${deviceTypeId}/ota-update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            force_update: false,
            user_id: 1 // TODO: Pegar do usuário logado
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentOtaId = data.ota_log_id;
            showOtaSuccess(data);
            startOtaPolling(currentOtaId);
        } else {
            showOtaError(data.message || 'Erro ao iniciar atualização OTA');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showOtaError('Erro de conexão ao iniciar OTA');
    });
}

function showOtaModal(content, type = 'info') {
    const modal = document.getElementById('otaModal');
    const contentDiv = document.getElementById('otaContent');
    
    contentDiv.innerHTML = `
        <div class="ota-status ${type}">
            ${content}
        </div>
    `;
    
    modal.classList.add('show');
}

function showOtaSuccess(data) {
    const contentDiv = document.getElementById('otaContent');
    
    contentDiv.innerHTML = `
        <div class="ota-status success">
            ✅ Comandos OTA enviados com sucesso!
        </div>
        <div style="margin: 1rem 0;">
            <strong>Detalhes:</strong><br>
            📊 Dispositivos encontrados: ${data.devices_count}<br>
            🔄 Versão do firmware: ${data.firmware_version}<br>
            📝 Log ID: ${data.ota_log_id}
        </div>
        <div class="ota-progress">
            <div class="ota-progress-bar pulse" style="width: 25%;"></div>
        </div>
        <div id="otaLogContainer" class="ota-log">
            <div>🚀 Update OTA iniciado...</div>
            <div>📡 Comandos MQTT enviados para ${data.devices_count} dispositivos</div>
            <div>⏳ Aguardando resposta dos dispositivos...</div>
        </div>
    `;
}

function showOtaError(message) {
    const contentDiv = document.getElementById('otaContent');
    
    contentDiv.innerHTML = `
        <div class="ota-status error">
            ❌ ${message}
        </div>
        <div style="margin: 1rem 0;">
            <strong>Possíveis causas:</strong><br>
            • Servidor nginx OTA não configurado<br>
            • Nenhum dispositivo deste tipo cadastrado<br>
            • Firmware não disponível<br>
            • Erro de conectividade
        </div>
        <div style="background-color: #f3f4f6; padding: 1rem; border-radius: 0.375rem; margin: 1rem 0;">
            <strong>💡 Para configurar o servidor OTA:</strong><br>
            <code>sudo ./setup-nginx-ota.sh</code><br>
            <code>sudo ./create-firmware-structure.sh</code>
        </div>
    `;
}

function startOtaPolling(otaId) {
    if (otaPollingInterval) {
        clearInterval(otaPollingInterval);
    }
    
    otaPollingInterval = setInterval(() => {
        fetch(`/ota-updates/${otaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateOtaProgress(data.data);
                    
                    // Parar polling se concluído
                    if (['completed', 'failed', 'cancelled'].includes(data.data.status)) {
                        clearInterval(otaPollingInterval);
                        otaPollingInterval = null;
                    }
                }
            })
            .catch(error => {
                console.error('Erro no polling OTA:', error);
            });
    }, 3000); // Poll a cada 3 segundos
}

function updateOtaProgress(otaData) {
    const logContainer = document.getElementById('otaLogContainer');
    const progressBar = document.querySelector('.ota-progress-bar');
    
    if (!logContainer || !progressBar) return;
    
    // Atualizar barra de progresso
    const progress = otaData.success_rate || 0;
    progressBar.style.width = `${Math.max(progress, 25)}%`;
    
    if (otaData.status === 'completed') {
        progressBar.style.width = '100%';
        progressBar.classList.remove('pulse');
        progressBar.style.backgroundColor = '#16a34a';
    } else if (otaData.status === 'failed') {
        progressBar.style.backgroundColor = '#dc2626';
        progressBar.classList.remove('pulse');
    }
    
    // Atualizar log
    logContainer.innerHTML = `
        <div>🚀 Update OTA iniciado...</div>
        <div>📡 Comandos MQTT enviados para ${otaData.devices_count} dispositivos</div>
        <div>✅ Sucessos: ${otaData.successful_devices}</div>
        <div>❌ Falhas: ${otaData.failed_devices}</div>
        <div>📊 Taxa de sucesso: ${otaData.success_rate}%</div>
        <div>⏱️ Status: ${getStatusLabel(otaData.status)}</div>
        ${otaData.error_message ? `<div style="color: #dc2626;">❌ Erro: ${otaData.error_message}</div>` : ''}
        <div>🕐 Última atualização: ${new Date().toLocaleTimeString()}</div>
    `;
}

function getStatusLabel(status) {
    const labels = {
        'initiated': '🔄 Iniciado',
        'in_progress': '⏳ Em progresso',
        'completed': '✅ Concluído',
        'failed': '❌ Falhou',
        'cancelled': '⏸️ Cancelado'
    };
    return labels[status] || status;
}

function closeOtaModal() {
    const modal = document.getElementById('otaModal');
    modal.classList.remove('show');
    
    if (otaPollingInterval) {
        clearInterval(otaPollingInterval);
        otaPollingInterval = null;
    }
    
    currentOtaId = null;
}

// Fechar modal ao clicar fora
document.getElementById('otaModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeOtaModal();
    }
});
</script>
@endsection 