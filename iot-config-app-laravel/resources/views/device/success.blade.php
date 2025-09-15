@extends('layouts.app')

@section('title', 'Configuração Concluída')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="success-container">
                <div class="success-card">
                    <div class="success-header">
                        <h1>🎉 Configuração Concluída!</h1>
                        <p class="success-subtitle">Seu dispositivo IoT foi configurado com sucesso</p>
                    </div>
                    
                    <div class="success-content">
                        <div class="status-check">
                            <div class="check-item completed">
                                <span class="check-icon">✅</span>
                                <span class="check-text">Dispositivo conectado à nova rede WiFi</span>
                            </div>
                            <div class="check-item completed">
                                <span class="check-icon">✅</span>
                                <span class="check-text">Configurações salvas no dispositivo</span>
                            </div>
                            <div class="check-item completed">
                                <span class="check-icon">✅</span>
                                <span class="check-text">Tópico MQTT criado no sistema</span>
                            </div>
                            <div class="check-item completed">
                                <span class="check-icon">✅</span>
                                <span class="check-text">Transição de rede concluída</span>
                            </div>
                        </div>
                        
                        <div class="next-actions">
                            <h3>🚀 Próximos Passos:</h3>
                            <div class="action-cards">
                                <div class="action-card">
                                    <h4>📱 Usar o Aplicativo</h4>
                                    <p>Agora você pode monitorar e controlar seu dispositivo através do aplicativo móvel.</p>
                                    <a href="#" onclick="openMobileApp()" class="btn btn-primary">
                                        📱 Abrir Aplicativo
                                    </a>
                                </div>
                                
                                <div class="action-card">
                                    <h4>💻 Painel Web</h4>
                                    <p>Acesse o painel web para configurações avançadas e monitoramento detalhado.</p>
                                    <a href="{{ route('home') }}" class="btn btn-secondary">
                                        💻 Ir para o Painel
                                    </a>
                                </div>
                                
                                <div class="action-card">
                                    <h4>➕ Adicionar Outro Dispositivo</h4>
                                    <p>Configure mais dispositivos IoT para expandir seu sistema.</p>
                                    <a href="{{ route('device.add') }}" class="btn btn-outline-primary">
                                        ➕ Novo Dispositivo
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="device-info" id="deviceInfo" style="display: none;">
                            <h3>📋 Informações do Dispositivo</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <strong>Nome:</strong>
                                    <span id="deviceName">-</span>
                                </div>
                                <div class="info-item">
                                    <strong>MAC Address:</strong>
                                    <span id="deviceMacAddress" class="mono">-</span>
                                </div>
                                <div class="info-item">
                                    <strong>Rede WiFi:</strong>
                                    <span id="deviceSSID">-</span>
                                </div>
                                <div class="info-item">
                                    <strong>Tópico MQTT:</strong>
                                    <span id="deviceTopic">-</span>
                                </div>
                                <div class="info-item">
                                    <strong>Configurado em:</strong>
                                    <span id="deviceTimestamp">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-container {
    padding: 2rem 1rem;
    min-height: 80vh;
    display: flex;
    align-items: center;
}

.success-card {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
    text-align: center;
    width: 100%;
}

.success-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.success-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

.success-content {
    text-align: left;
}

.status-check {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.check-item {
    display: flex;
    align-items: center;
    margin: 0.75rem 0;
}

.check-icon {
    font-size: 1.5rem;
    margin-right: 1rem;
    min-width: 2rem;
}

.check-text {
    font-size: 1.1rem;
}

.next-actions h3 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: white;
}

.action-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.action-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    backdrop-filter: blur(10px);
}

.action-card h4 {
    color: white;
    margin-bottom: 0.5rem;
}

.action-card p {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.device-info {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 1.5rem;
    margin-top: 2rem;
}

.device-info h3 {
    color: white;
    margin-bottom: 1rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.info-item {
    background: rgba(255, 255, 255, 0.1);
    padding: 0.75rem;
    border-radius: 6px;
}

.info-item strong {
    display: block;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.info-item span {
    color: white;
    font-weight: 500;
}

.mono {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.9rem;
    background: rgba(255, 255, 255, 0.1);
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
}

.btn {
    border-radius: 6px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    border: 2px solid #3b82f6;
}

.btn-primary:hover {
    background: #2563eb;
    border-color: #2563eb;
    color: white;
    text-decoration: none;
}

.btn-secondary {
    background: #6b7280;
    color: white;
    border: 2px solid #6b7280;
}

.btn-secondary:hover {
    background: #4b5563;
    border-color: #4b5563;
    color: white;
    text-decoration: none;
}

.btn-outline-primary {
    background: transparent;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.5);
}

.btn-outline-primary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: white;
    color: white;
    text-decoration: none;
}

@media (max-width: 768px) {
    .success-card {
        padding: 1.5rem;
    }
    
    .success-header h1 {
        font-size: 2rem;
    }
    
    .action-cards {
        grid-template-columns: 1fr;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Tentar abrir aplicativo móvel
function openMobileApp() {
    // Obter MAC address do localStorage se disponível
    const deviceData = localStorage.getItem('device_config_data');
    let macAddress = '';
    
    if (deviceData) {
        try {
            const data = JSON.parse(deviceData);
            macAddress = data.macAddress || '';
        } catch (error) {
            console.log('Erro ao obter MAC address:', error);
        }
    }
    
    const encodedMac = encodeURIComponent(macAddress);
    
    const appUrls = [
        `iotconfig://success?mac=${encodedMac}`,                 // Esquema customizado com MAC
        `iotconfig://device/configured?mac=${encodedMac}`,       // Com parâmetros completos
        `https://app.iotconfig.com/success?mac=${encodedMac}`,   // Web app com MAC
        window.location.origin + '/dashboard'                   // Fallback local
    ];
    
    let appOpened = false;
    
    // Tentar abrir o aplicativo
    for (let url of appUrls) {
        if (!appOpened) {
            try {
                window.location.href = url;
                appOpened = true;
                break;
            } catch (error) {
                console.log('Falha ao abrir:', url);
                continue;
            }
        }
    }
    
    // Fallback: mostrar instruções
    if (!appOpened) {
        alert('💡 Se você tem o aplicativo instalado, ele deve abrir automaticamente. Caso contrário, baixe-o na App Store ou Google Play.');
    }
}

// Carregar informações do dispositivo se disponíveis
document.addEventListener('DOMContentLoaded', function() {
    const deviceData = localStorage.getItem('device_config_data');
    
    if (deviceData) {
        try {
            const data = JSON.parse(deviceData);
            const deviceInfo = document.getElementById('deviceInfo');
            
            document.getElementById('deviceName').textContent = data.name || '-';
            document.getElementById('deviceMacAddress').textContent = data.macAddress || '-';
            document.getElementById('deviceSSID').textContent = data.ssid || '-';
            document.getElementById('deviceTopic').textContent = data.topic || '-';
            document.getElementById('deviceTimestamp').textContent = data.timestamp || '-';
            
            deviceInfo.style.display = 'block';
        } catch (error) {
            console.log('Erro ao carregar dados do dispositivo:', error);
        }
    }
    
    // Limpar dados após 5 minutos para privacidade
    setTimeout(() => {
        localStorage.removeItem('device_config_data');
        localStorage.removeItem('pending_wifi_config');
        localStorage.removeItem('device_config_completed');
    }, 300000);
});
</script>
@endsection 