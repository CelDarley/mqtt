@extends('layouts.app')

@section('title', 'Ativar Dispositivo IoT')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">⚡ Ativar Dispositivo IoT</h1>
                <a href="{{ route('pending-devices.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <!-- Informações do Dispositivo -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-microchip"></i> Informações do Dispositivo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nome do Dispositivo</label>
                                <div class="fw-bold">{{ $device['device_name'] ?? 'Sem nome' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">MAC Address</label>
                                <div class="font-monospace">{{ strtoupper($device['mac_address'] ?? 'N/A') }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Status Atual</label>
                                <div>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> Pendente
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Endereço IP</label>
                                <div>{{ $device['ip_address'] ?? 'N/A' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Rede WiFi</label>
                                <div>{{ $device['wifi_ssid'] ?? 'N/A' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Registrado em</label>
                                <div>
                                    @if(!empty($device['registered_at']))
                                        @php
                                            $registeredAt = \Carbon\Carbon::parse($device['registered_at']);
                                        @endphp
                                        {{ $registeredAt->format('d/m/Y H:i:s') }}
                                        <small class="text-muted d-block">{{ $registeredAt->diffForHumans() }}</small>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if(!empty($device['device_info']))
                        <hr>
                        <h6 class="text-muted mb-3">Informações Técnicas</h6>
                        <div class="row">
                            @if(!empty($device['device_info']['firmware_version']))
                                <div class="col-md-4">
                                    <small class="text-muted">Firmware</small>
                                    <div>{{ $device['device_info']['firmware_version'] }}</div>
                                </div>
                            @endif
                            @if(!empty($device['device_info']['esp32_model']))
                                <div class="col-md-4">
                                    <small class="text-muted">Modelo</small>
                                    <div>{{ $device['device_info']['esp32_model'] }}</div>
                                </div>
                            @endif
                            @if(!empty($device['device_info']['free_heap']))
                                <div class="col-md-4">
                                    <small class="text-muted">Memória Livre</small>
                                    <div>{{ number_format($device['device_info']['free_heap'] / 1024, 2) }} KB</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Formulário de Ativação -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs"></i> Configuração para Ativação
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('pending-devices.process-activation', $device['id']) }}" method="POST" id="activationForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="device_type" class="form-label">
                                        <i class="fas fa-tags"></i> Tipo do Dispositivo *
                                    </label>
                                    <select name="device_type" id="device_type" class="form-select @error('device_type') is-invalid @enderror" required>
                                        <option value="">Selecione o tipo</option>
                                        <option value="sensor" {{ old('device_type') == 'sensor' ? 'selected' : '' }}>
                                            📊 Sensor
                                        </option>
                                        <option value="atuador" {{ old('device_type') == 'atuador' ? 'selected' : '' }}>
                                            ⚙️ Atuador
                                        </option>
                                        <option value="controlador" {{ old('device_type') == 'controlador' ? 'selected' : '' }}>
                                            🎛️ Controlador
                                        </option>
                                        <option value="monitor" {{ old('device_type') == 'monitor' ? 'selected' : '' }}>
                                            📺 Monitor
                                        </option>
                                    </select>
                                    @error('device_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Defina a função principal do dispositivo</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department" class="form-label">
                                        <i class="fas fa-building"></i> Departamento *
                                    </label>
                                    <input type="text" 
                                           name="department" 
                                           id="department" 
                                           class="form-control @error('department') is-invalid @enderror" 
                                           value="{{ old('department') }}"
                                           placeholder="Ex: producao, qualidade, laboratorio"
                                           required>
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Departamento onde o dispositivo será utilizado</div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview do Tópico MQTT -->
                        <div class="alert alert-info" id="topicPreview" style="display: none;">
                            <h6 class="alert-heading">
                                <i class="fas fa-share-alt"></i> Tópico MQTT que será criado:
                            </h6>
                            <code id="topicName">iot/[departamento]/[tipo]/[mac_address]</code>
                            <small class="d-block mt-2 text-muted">
                                Este tópico será usado para comunicação MQTT com o dispositivo
                            </small>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('pending-devices.index') }}" class="btn btn-outline-secondary me-md-2">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-success" id="activateBtn">
                                        <i class="fas fa-rocket"></i> Ativar Dispositivo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="card border-0 bg-light mt-4">
                <div class="card-body">
                    <h6 class="text-primary">
                        <i class="fas fa-info-circle"></i> O que acontece após a ativação?
                    </h6>
                    <ul class="small mb-0">
                        <li><strong>Tópico MQTT criado:</strong> Um tópico único será gerado para este dispositivo</li>
                        <li><strong>Configuração enviada:</strong> O ESP32 receberá automaticamente as configurações MQTT</li>
                        <li><strong>Comunicação ativa:</strong> O dispositivo começará a enviar dados via MQTT</li>
                        <li><strong>Monitoramento:</strong> Você poderá acompanhar os dados no sistema</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deviceTypeSelect = document.getElementById('device_type');
    const departmentInput = document.getElementById('department');
    const topicPreview = document.getElementById('topicPreview');
    const topicName = document.getElementById('topicName');
    const activationForm = document.getElementById('activationForm');
    const activateBtn = document.getElementById('activateBtn');
    
    const macAddress = '{{ $device["mac_address"] ?? "" }}';
    const macForTopic = macAddress.replace(/:/g, '').toLowerCase();

    // Atualizar preview do tópico
    function updateTopicPreview() {
        const deviceType = deviceTypeSelect.value;
        const department = departmentInput.value.toLowerCase().trim();
        
        if (deviceType && department) {
            const topic = `iot/${department}/${deviceType}/${macForTopic}`;
            topicName.textContent = topic;
            topicPreview.style.display = 'block';
        } else {
            topicPreview.style.display = 'none';
        }
    }

    // Event listeners para atualizar preview
    deviceTypeSelect.addEventListener('change', updateTopicPreview);
    departmentInput.addEventListener('input', updateTopicPreview);

    // Submissão do formulário
    activationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!deviceTypeSelect.value || !departmentInput.value.trim()) {
            alert('Por favor, preencha todos os campos obrigatórios.');
            return;
        }

        // Confirmar ativação
        const deviceName = '{{ $device["device_name"] ?? "Dispositivo" }}';
        const topicText = topicName.textContent;
        
        const confirmed = confirm(
            `Confirma a ativação do dispositivo "${deviceName}"?\n\n` +
            `Tópico MQTT: ${topicText}\n\n` +
            `Esta ação criará o tópico e enviará as configurações para o ESP32.`
        );
        
        if (confirmed) {
            // Mostrar loading
            activateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ativando...';
            activateBtn.disabled = true;
            
            // Submeter formulário
            this.submit();
        }
    });

    // Inicializar preview se campos já estiverem preenchidos
    updateTopicPreview();
});

// Sugestões para departamento
const departmentSuggestions = [
    'producao',
    'qualidade', 
    'manutencao',
    'laboratorio',
    'almoxarifado',
    'logistica',
    'engenharia'
];

// Adicionar autocomplete para departamento
document.getElementById('department').addEventListener('input', function(e) {
    const value = e.target.value.toLowerCase();
    
    // Encontrar sugestões
    const suggestions = departmentSuggestions.filter(dept => 
        dept.includes(value) && dept !== value
    );
    
    if (suggestions.length > 0 && value.length > 1) {
        // Criar/atualizar datalist
        let datalist = document.getElementById('departmentSuggestions');
        if (!datalist) {
            datalist = document.createElement('datalist');
            datalist.id = 'departmentSuggestions';
            document.body.appendChild(datalist);
            e.target.setAttribute('list', 'departmentSuggestions');
        }
        
        datalist.innerHTML = '';
        suggestions.forEach(suggestion => {
            const option = document.createElement('option');
            option.value = suggestion;
            datalist.appendChild(option);
        });
    }
});
</script>
@endsection 