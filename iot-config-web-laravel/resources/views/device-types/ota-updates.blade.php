@extends('layouts.app')

@section('title', 'Logs OTA - Atualizações de Firmware')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="page-header-content">
            <h1>📊 Logs OTA - Atualizações de Firmware</h1>
            <p>Histórico detalhado de atualizações over-the-air</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('device-types.index') }}" class="btn btn-outline">
                ← Voltar para Tipos de Dispositivos
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

    <!-- Filtros -->
    <div class="filters-section">
        <form method="GET" action="{{ route('ota-updates.index') }}" class="filters-form">
            <div class="filter-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="">Todos</option>
                    <option value="initiated" {{ request('status') == 'initiated' ? 'selected' : '' }}>🔄 Iniciado</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>⏳ Em Progresso</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✅ Concluído</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>❌ Falhou</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>⏸️ Cancelado</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="days">Período:</label>
                <select name="days" id="days">
                    <option value="7" {{ request('days', '7') == '7' ? 'selected' : '' }}>Últimos 7 dias</option>
                    <option value="30" {{ request('days') == '30' ? 'selected' : '' }}>Últimos 30 dias</option>
                    <option value="90" {{ request('days') == '90' ? 'selected' : '' }}>Últimos 90 dias</option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Lista de Updates OTA -->
    <div class="table-container">
        @if(isset($updates['data']) && count($updates['data']) > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo de Dispositivo</th>
                        <th>Versão Firmware</th>
                        <th>Status</th>
                        <th>Dispositivos</th>
                        <th>Taxa Sucesso</th>
                        <th>Duração</th>
                        <th>Data/Hora</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($updates['data'] as $update)
                        <tr class="ota-row">
                            <td>
                                <strong>#{{ $update['id'] }}</strong>
                            </td>
                            <td>
                                <span class="device-type">{{ $update['device_type'] }}</span>
                            </td>
                            <td>
                                <span class="version-badge">{{ $update['firmware_version'] }}</span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $update['status'] }}">
                                    {{ getStatusLabel($update['status']) }}
                                </span>
                            </td>
                            <td>
                                <div class="devices-info">
                                    <div class="devices-total">{{ $update['devices_count'] }} total</div>
                                    @if($update['successful_devices'] > 0 || $update['failed_devices'] > 0)
                                        <div class="devices-breakdown">
                                            ✅ {{ $update['successful_devices'] }} / ❌ {{ $update['failed_devices'] }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($update['devices_count'] > 0)
                                    <div class="success-rate">
                                        <div class="rate-bar">
                                            <div class="rate-fill" style="width: {{ $update['success_rate'] }}%"></div>
                                        </div>
                                        <span class="rate-text">{{ $update['success_rate'] }}%</span>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($update['duration_minutes'])
                                    {{ $update['duration_minutes'] }} min
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="datetime-info">
                                    <div class="date">{{ \Carbon\Carbon::parse($update['created_at'])->format('d/m/Y') }}</div>
                                    <div class="time">{{ \Carbon\Carbon::parse($update['created_at'])->format('H:i:s') }}</div>
                                </div>
                            </td>
                            <td class="actions">
                                <button onclick="viewOtaDetails({{ $update['id'] }})" 
                                        class="btn-action btn-view" 
                                        title="Ver Detalhes">
                                    👁️
                                </button>
                                
                                @if($update['status'] === 'in_progress' || $update['status'] === 'initiated')
                                    <button onclick="refreshOtaStatus({{ $update['id'] }})" 
                                            class="btn-action btn-refresh" 
                                            title="Atualizar Status">
                                        🔄
                                    </button>
                                @endif
                                
                                @if($update['error_message'])
                                    <button onclick="showError('{{ addslashes($update['error_message']) }}')" 
                                            class="btn-action btn-error" 
                                            title="Ver Erro">
                                        ⚠️
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Paginação -->
            @if(isset($updates['pagination']))
                <div class="pagination-info">
                    Mostrando {{ count($updates['data']) }} de {{ $updates['pagination']['total'] ?? 0 }} registros
                </div>
            @endif
        @else
            <div class="empty-state">
                <p>📭 Nenhum update OTA encontrado para os filtros selecionados.</p>
                <a href="{{ route('device-types.index') }}" class="btn btn-primary">
                    🔄 Iniciar Primeira Atualização
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Modal de Detalhes OTA -->
<div id="otaDetailsModal" class="ota-modal">
    <div class="ota-modal-content large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; color: #1f2937;">📊 Detalhes do Update OTA</h3>
            <button onclick="closeDetailsModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <div id="otaDetailsContent">
            <!-- Conteúdo será preenchido dinamicamente -->
        </div>
        
        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
            <button onclick="closeDetailsModal()" class="btn btn-secondary">Fechar</button>
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.filters-section {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin-bottom: 2rem;
}

.filters-form {
    display: flex;
    gap: 1rem;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.filter-group select,
.filter-group input {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    min-width: 120px;
}

.table-container {
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background-color: #f9fafb;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.ota-row:hover {
    background-color: #f9fafb;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-initiated { background-color: #dbeafe; color: #1e40af; }
.status-in_progress { background-color: #fef3c7; color: #92400e; }
.status-completed { background-color: #dcfce7; color: #166534; }
.status-failed { background-color: #fee2e2; color: #991b1b; }
.status-cancelled { background-color: #f3f4f6; color: #6b7280; }

.version-badge {
    background-color: #e0e7ff;
    color: #3730a3;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.875rem;
}

.devices-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.devices-total {
    font-weight: 600;
}

.devices-breakdown {
    font-size: 0.875rem;
    color: #6b7280;
}

.success-rate {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rate-bar {
    width: 60px;
    height: 8px;
    background-color: #f3f4f6;
    border-radius: 4px;
    overflow: hidden;
}

.rate-fill {
    height: 100%;
    background-color: #16a34a;
    transition: width 0.3s ease;
}

.rate-text {
    font-size: 0.875rem;
    font-weight: 600;
}

.datetime-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.date {
    font-weight: 600;
}

.time {
    font-size: 0.875rem;
    color: #6b7280;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    margin: 0 0.125rem;
    transition: all 0.2s ease;
}

.btn-view {
    background-color: #3b82f6;
    color: white;
}

.btn-refresh {
    background-color: #16a34a;
    color: white;
}

.btn-error {
    background-color: #dc2626;
    color: white;
}

.btn-action:hover {
    transform: scale(1.05);
    opacity: 0.9;
}

.ota-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.ota-modal-content.large {
    background-color: white;
    margin: 2% auto;
    padding: 2rem;
    border-radius: 0.5rem;
    width: 95%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.ota-modal.show {
    display: block;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.pagination-info {
    padding: 1rem;
    text-align: center;
    color: #6b7280;
    border-top: 1px solid #f3f4f6;
}

.text-muted {
    color: #9ca3af;
}

.alert {
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    text-decoration: none;
    font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
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

.device-type {
    font-weight: 600;
    color: #1f2937;
}

@media (max-width: 768px) {
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .data-table {
        font-size: 0.875rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.5rem;
    }
}
</style>

<script>
function getStatusLabel(status) {
    const labels = {
        'initiated': '🔄 Iniciado',
        'in_progress': '⏳ Em Progresso',
        'completed': '✅ Concluído',
        'failed': '❌ Falhou',
        'cancelled': '⏸️ Cancelado'
    };
    return labels[status] || status;
}

function viewOtaDetails(otaId) {
    // Mostrar modal de loading
    const modal = document.getElementById('otaDetailsModal');
    const content = document.getElementById('otaDetailsContent');
    
    content.innerHTML = '<div style="text-align: center; padding: 2rem;">🔄 Carregando detalhes...</div>';
    modal.classList.add('show');
    
    // Buscar detalhes do OTA
    fetch(`/ota-updates/${otaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOtaDetails(data.data);
            } else {
                content.innerHTML = '<div class="alert alert-error">Erro ao carregar detalhes do OTA</div>';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            content.innerHTML = '<div class="alert alert-error">Erro de conexão</div>';
        });
}

function displayOtaDetails(otaData) {
    const content = document.getElementById('otaDetailsContent');
    
    const deviceResults = otaData.device_results || {};
    const resultsList = Object.entries(deviceResults).map(([deviceId, result]) => {
        const statusIcon = result.status === 'success' ? '✅' : result.status === 'failed' ? '❌' : '⏳';
        return `
            <div class="device-result">
                <div class="device-id">${statusIcon} ${deviceId}</div>
                <div class="device-status">${result.message || 'Status: ' + result.status}</div>
                <div class="device-timestamp">${new Date(result.timestamp).toLocaleString()}</div>
            </div>
        `;
    }).join('');
    
    content.innerHTML = `
        <div class="ota-details-grid">
            <div class="details-section">
                <h4>📊 Informações Gerais</h4>
                <div class="detail-item">
                    <label>ID do Update:</label>
                    <span>#${otaData.id}</span>
                </div>
                <div class="detail-item">
                    <label>Tipo de Dispositivo:</label>
                    <span>${otaData.device_type}</span>
                </div>
                <div class="detail-item">
                    <label>Versão do Firmware:</label>
                    <span class="version-badge">${otaData.firmware_version}</span>
                </div>
                <div class="detail-item">
                    <label>Status:</label>
                    <span class="status-badge status-${otaData.status}">${getStatusLabel(otaData.status)}</span>
                </div>
                <div class="detail-item">
                    <label>Início:</label>
                    <span>${otaData.started_at ? new Date(otaData.started_at).toLocaleString() : 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <label>Conclusão:</label>
                    <span>${otaData.completed_at ? new Date(otaData.completed_at).toLocaleString() : 'Em andamento'}</span>
                </div>
                <div class="detail-item">
                    <label>Duração:</label>
                    <span>${otaData.duration_minutes ? otaData.duration_minutes + ' minutos' : 'N/A'}</span>
                </div>
            </div>
            
            <div class="details-section">
                <h4>📈 Estatísticas</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">${otaData.devices_count}</div>
                        <div class="stat-label">Total Dispositivos</div>
                    </div>
                    <div class="stat-item success">
                        <div class="stat-value">${otaData.successful_devices}</div>
                        <div class="stat-label">Sucessos</div>
                    </div>
                    <div class="stat-item failed">
                        <div class="stat-value">${otaData.failed_devices}</div>
                        <div class="stat-label">Falhas</div>
                    </div>
                    <div class="stat-item rate">
                        <div class="stat-value">${otaData.success_rate}%</div>
                        <div class="stat-label">Taxa Sucesso</div>
                    </div>
                </div>
            </div>
        </div>
        
        ${otaData.error_message ? `
            <div class="details-section">
                <h4>⚠️ Erro</h4>
                <div class="error-message">${otaData.error_message}</div>
            </div>
        ` : ''}
        
        <div class="details-section">
            <h4>📱 Resultados por Dispositivo</h4>
            <div class="device-results">
                ${resultsList || '<div class="text-muted">Nenhum resultado de dispositivo disponível</div>'}
            </div>
        </div>
    `;
}

function refreshOtaStatus(otaId) {
    // Recarregar a página para atualizar o status
    window.location.reload();
}

function showError(errorMessage) {
    alert('Erro: ' + errorMessage);
}

function closeDetailsModal() {
    document.getElementById('otaDetailsModal').classList.remove('show');
}

// Fechar modal ao clicar fora
document.getElementById('otaDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetailsModal();
    }
});

// Auto-refresh para updates em progresso
setInterval(() => {
    const inProgressRows = document.querySelectorAll('.status-in_progress, .status-initiated');
    if (inProgressRows.length > 0) {
        // Recarregar página se houver updates em progresso
        window.location.reload();
    }
}, 30000); // 30 segundos
</script>

@php
function getStatusLabel($status) {
    $labels = [
        'initiated' => '🔄 Iniciado',
        'in_progress' => '⏳ Em Progresso', 
        'completed' => '✅ Concluído',
        'failed' => '❌ Falhou',
        'cancelled' => '⏸️ Cancelado'
    ];
    return $labels[$status] ?? $status;
}
@endphp

@endsection 