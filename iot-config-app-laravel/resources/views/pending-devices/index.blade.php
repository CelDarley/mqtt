@extends('layouts.app')

@section('title', 'Dispositivos IoT - Pendentes')

@section('content')
<div class="container-fluid">
    <!-- Header com Estatísticas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">📱 Dispositivos IoT</h1>
                <button class="btn btn-outline-primary" onclick="refreshDevices()">
                    <i class="fas fa-sync-alt"></i> Atualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    @if(!empty($stats))
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center border-0 bg-light">
                <div class="card-body py-3">
                    <h4 class="text-primary mb-1">{{ $stats['total'] ?? 0 }}</h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 bg-warning bg-opacity-10">
                <div class="card-body py-3">
                    <h4 class="text-warning mb-1">{{ $stats['pending'] ?? 0 }}</h4>
                    <small class="text-muted">Pendentes</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 bg-success bg-opacity-10">
                <div class="card-body py-3">
                    <h4 class="text-success mb-1">{{ $stats['activated'] ?? 0 }}</h4>
                    <small class="text-muted">Ativados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 bg-danger bg-opacity-10">
                <div class="card-body py-3">
                    <h4 class="text-danger mb-1">{{ $stats['rejected'] ?? 0 }}</h4>
                    <small class="text-muted">Rejeitados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 bg-info bg-opacity-10">
                <div class="card-body py-3">
                    <h4 class="text-info mb-1">{{ $stats['recent'] ?? 0 }}</h4>
                    <small class="text-muted">Últimas 24h</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 bg-secondary bg-opacity-10">
                <div class="card-body py-3">
                    <h4 class="text-secondary mb-1">{{ $stats['today'] ?? 0 }}</h4>
                    <small class="text-muted">Hoje</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Como Funciona -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <i class="fas fa-info-circle"></i> Como Funciona
                    </h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary rounded-circle me-2">1</span>
                                <small><strong>ESP32 se conecta ao WiFi</strong></small>
                            </div>
                            <small class="text-muted">O dispositivo configura a rede via Captive Portal</small>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary rounded-circle me-2">2</span>
                                <small><strong>Registro automático</strong></small>
                            </div>
                            <small class="text-muted">O dispositivo se registra automaticamente no sistema</small>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary rounded-circle me-2">3</span>
                                <small><strong>Aparece na lista abaixo</strong></small>
                            </div>
                            <small class="text-muted">Dispositivo fica pendente aguardando ativação</small>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary rounded-circle me-2">4</span>
                                <small><strong>Ativar manualmente</strong></small>
                            </div>
                            <small class="text-muted">Configure tipo e departamento para ativar</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error') || isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') ?? $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Lista de Dispositivos -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">📋 Lista de Dispositivos</h5>
                </div>
                <div class="card-body p-0">
                    @if(empty($devices))
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum dispositivo encontrado</h5>
                            <p class="text-muted">Os dispositivos que se conectarem aparecerão aqui automaticamente.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="devicesTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0">Dispositivo</th>
                                        <th class="border-0">Rede & IP</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0">Registrado</th>
                                        <th class="border-0 text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($devices as $device)
                                    <tr>
                                        <td>
                                            <div>
                                                <h6 class="mb-1">{{ $device['device_name'] ?? 'Sem nome' }}</h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-ethernet"></i> 
                                                    {{ strtoupper($device['mac_address'] ?? 'N/A') }}
                                                </small>
                                                @if(!empty($device['device_info']['firmware_version']))
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-microchip"></i> 
                                                        {{ $device['device_info']['firmware_version'] }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                @if(!empty($device['wifi_ssid']))
                                                    <small class="d-block">
                                                        <i class="fas fa-wifi"></i> {{ $device['wifi_ssid'] }}
                                                    </small>
                                                @endif
                                                @if(!empty($device['ip_address']))
                                                    <small class="text-muted">
                                                        <i class="fas fa-network-wired"></i> {{ $device['ip_address'] }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match($device['status'] ?? 'pending') {
                                                    'pending' => 'warning',
                                                    'activated' => 'success',
                                                    'rejected' => 'danger',
                                                    default => 'secondary'
                                                };
                                                $statusIcon = match($device['status'] ?? 'pending') {
                                                    'pending' => 'clock',
                                                    'activated' => 'check-circle',
                                                    'rejected' => 'times-circle',
                                                    default => 'question-circle'
                                                };
                                                $statusText = match($device['status'] ?? 'pending') {
                                                    'pending' => 'Pendente',
                                                    'activated' => 'Ativado',
                                                    'rejected' => 'Rejeitado',
                                                    default => 'Desconhecido'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">
                                                <i class="fas fa-{{ $statusIcon }}"></i> {{ $statusText }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(!empty($device['registered_at']))
                                                @php
                                                    $registeredAt = \Carbon\Carbon::parse($device['registered_at']);
                                                @endphp
                                                <small class="d-block">{{ $registeredAt->format('d/m/Y') }}</small>
                                                <small class="text-muted">{{ $registeredAt->format('H:i:s') }}</small>
                                                <br><small class="text-muted">{{ $registeredAt->diffForHumans() }}</small>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                @if($device['status'] === 'pending')
                                                    <a href="{{ route('pending-devices.activate', $device['id']) }}" 
                                                       class="btn btn-success btn-sm" title="Ativar Dispositivo">
                                                        <i class="fas fa-play"></i>
                                                    </a>
                                                    <button onclick="rejectDevice({{ $device['id'] }})" 
                                                            class="btn btn-outline-danger btn-sm" title="Rejeitar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ route('pending-devices.show', $device['id']) }}" 
                                                       class="btn btn-outline-primary btn-sm" title="Ver Detalhes">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                                <button onclick="deleteDevice({{ $device['id'] }})" 
                                                        class="btn btn-outline-secondary btn-sm" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Carregando...</span>
    </div>
</div>

<style>
#loadingOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.badge {
    font-size: 0.75em;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.btn-group .btn {
    border-radius: 0.375rem !important;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>

<script>
// Atualizar lista de dispositivos
async function refreshDevices() {
    const overlay = document.getElementById('loadingOverlay');
    overlay.classList.remove('d-none');
    
    try {
        const response = await fetch('{{ route("api.pending-devices.refresh") }}');
        const data = await response.json();
        
        if (data.success) {
            // Recarregar a página para atualizar os dados
            window.location.reload();
        } else {
            showAlert('danger', 'Erro ao atualizar lista: ' + data.message);
        }
    } catch (error) {
        showAlert('danger', 'Erro de conexão: ' + error.message);
    } finally {
        overlay.classList.add('d-none');
    }
}

// Rejeitar dispositivo
async function rejectDevice(deviceId) {
    if (!confirm('Tem certeza que deseja rejeitar este dispositivo?')) {
        return;
    }
    
    const overlay = document.getElementById('loadingOverlay');
    overlay.classList.remove('d-none');
    
    try {
        const response = await fetch(`{{ route('pending-devices.reject', ':id') }}`.replace(':id', deviceId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            showAlert('success', 'Dispositivo rejeitado com sucesso');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            const data = await response.json();
            showAlert('danger', data.message || 'Erro ao rejeitar dispositivo');
        }
    } catch (error) {
        showAlert('danger', 'Erro de conexão: ' + error.message);
    } finally {
        overlay.classList.add('d-none');
    }
}

// Excluir dispositivo
async function deleteDevice(deviceId) {
    if (!confirm('Tem certeza que deseja excluir este dispositivo? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    const overlay = document.getElementById('loadingOverlay');
    overlay.classList.remove('d-none');
    
    try {
        const response = await fetch(`{{ route('pending-devices.destroy', ':id') }}`.replace(':id', deviceId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            showAlert('success', 'Dispositivo excluído com sucesso');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            const data = await response.json();
            showAlert('danger', data.message || 'Erro ao excluir dispositivo');
        }
    } catch (error) {
        showAlert('danger', 'Erro de conexão: ' + error.message);
    } finally {
        overlay.classList.add('d-none');
    }
}

// Mostrar alert
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remover após 5 segundos
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Auto-refresh a cada 30 segundos
setInterval(refreshDevices, 30000);
</script>
@endsection 