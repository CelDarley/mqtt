@extends('layouts.app')

@section('title', 'Criar Tópico MQTT')

@section('content')
<div class="topic-container">
    <div class="topic-card">
        <div class="header">
            <h1>📡 Criar Tópico MQTT</h1>
            <p>Configure o tópico MQTT para seu dispositivo IoT</p>
        </div>

        {{-- Seção de dispositivo detectado - mostrada via JavaScript --}}
        <div class="device-detected" id="device-detected-section" style="display: none;">
            <div class="success-icon">✅</div>
            <h3>📟 Dispositivo ESP32 Detectado Automaticamente</h3>
            <div class="device-info">
                <div class="info-item">
                    <strong>🔗 MAC Address:</strong> <span id="detected-mac" class="mono">-</span>
                </div>
                <div class="info-item">
                    <strong>✅ Status:</strong> <span class="status-auto">Detectado automaticamente</span>
                </div>
            </div>
        </div>

        {{-- Seção mostrada quando MAC não é encontrado --}}
        <div class="device-not-detected" id="device-not-detected-section" style="display: none;">
            <div class="warning-icon">⚠️</div>
            <h3>Dispositivo ESP32 Não Detectado</h3>
            <p>Configure primeiro o ESP32 através do portal de configuração WiFi.</p>
            <p>Após conectar o ESP32 à rede WiFi, volte aqui para configurar o tópico MQTT.</p>
        </div>

        <form id="topicForm" action="{{ route('device.save-topic') }}" method="POST">
            @csrf
            
            <!-- Campos ocultos do dispositivo -->
            <input type="hidden" name="device_name" value="{{ $deviceName }}">
            <input type="hidden" name="mac_address" value="{{ $macAddress }}">
            
            @if(!$deviceName)
            <div class="form-group">
                <label for="device_name_input">📱 Nome do Dispositivo</label>
                <input type="text" id="device_name_input" name="device_name" required 
                       placeholder="Ex: Sensor Produção 01" 
                       value="{{ old('device_name') }}">
                @error('device_name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            @endif

            {{-- Campo MAC removido - agora é automático via localStorage --}}

            <div class="form-group">
                <label for="device_type">⚙️ Tipo do Dispositivo</label>
                <select id="device_type" name="device_type" required>
                    <option value="">Selecione o tipo</option>
                    <option value="sensor" {{ old('device_type') == 'sensor' ? 'selected' : '' }}>📊 Sensor</option>
                    <option value="atuador" {{ old('device_type') == 'atuador' ? 'selected' : '' }}>🔧 Atuador</option>
                    <option value="gateway" {{ old('device_type') == 'gateway' ? 'selected' : '' }}>📡 Gateway</option>
                    <option value="controlador" {{ old('device_type') == 'controlador' ? 'selected' : '' }}>🎛️ Controlador</option>
                </select>
                @error('device_type')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="department">🏢 Departamento</label>
                <select id="department" name="department" required>
                    <option value="">Selecione o departamento</option>
                    <option value="producao" {{ old('department') == 'producao' ? 'selected' : '' }}>🏭 Produção</option>
                    <option value="qualidade" {{ old('department') == 'qualidade' ? 'selected' : '' }}>✅ Qualidade</option>
                    <option value="manutencao" {{ old('department') == 'manutencao' ? 'selected' : '' }}>🔧 Manutenção</option>
                    <option value="administrativo" {{ old('department') == 'administrativo' ? 'selected' : '' }}>📋 Administrativo</option>
                </select>
                @error('department')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">📝 Descrição (Opcional)</label>
                <textarea id="description" name="description" rows="3" 
                          placeholder="Descreva a função ou localização do dispositivo...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="topic-preview">
                <h3>🏷️ Preview do Tópico</h3>
                <div class="topic-name" id="topicPreview">
                    iot/dispositivo/topic
                </div>
                <p class="topic-help">O nome do tópico será gerado automaticamente baseado nas informações acima</p>
            </div>

            <button type="submit" class="submit-button" id="submitBtn">
                💾 Criar Tópico MQTT
            </button>
        </form>

        <div class="navigation">
            <a href="{{ route('home') }}" class="btn-secondary">
                ⬅️ Voltar ao Dashboard
            </a>
        </div>
    </div>
</div>

<style>
.topic-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
}

.topic-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 2rem;
    color: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.header {
    text-align: center;
    margin-bottom: 2rem;
}

.header h1 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.header p {
    opacity: 0.9;
    font-size: 1.1rem;
}

.device-detected {
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(16, 185, 129, 0.4);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    text-align: center;
}

.success-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.device-detected h3 {
    margin-bottom: 1rem;
    color: #10b981;
}

.device-info {
    display: flex;
    gap: 2rem;
    justify-content: center;
    flex-wrap: wrap;
}

.info-item {
    background: rgba(255, 255, 255, 0.1);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #f0f9ff;
    font-size: 1.1rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.3);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.form-group select option {
    background: #1e293b;
    color: white;
}

.error {
    color: #fca5a5;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.device-not-detected {
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.4);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    text-align: center;
}

.device-not-detected h3 {
    margin-bottom: 1rem;
    color: #ef4444;
}

.warning-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.mono {
    font-family: 'Courier New', monospace;
    background: rgba(0, 0, 0, 0.3);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    color: #10b981;
}

.status-auto {
    color: #10b981;
    font-weight: 600;
}

.topic-preview {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 1.5rem;
    margin: 2rem 0;
    text-align: center;
}

.topic-preview h3 {
    margin-bottom: 1rem;
    color: #fbbf24;
}

.topic-name {
    background: rgba(0, 0, 0, 0.3);
    padding: 1rem;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 1.2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #10b981;
}

.topic-help {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    opacity: 0.8;
}

.submit-button {
    width: 100%;
    background: linear-gradient(45deg, #10b981, #059669);
    color: white;
    padding: 16px;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 1rem;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
}

.submit-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
}

.submit-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.navigation {
    text-align: center;
    margin-top: 2rem;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

@media (max-width: 768px) {
    .topic-container {
        padding: 1rem;
    }
    
    .topic-card {
        padding: 1.5rem;
    }
    
    .device-info {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

<script>
// Atualizar preview do tópico em tempo real
function updateTopicPreview() {
    const deviceName = document.getElementById('device_name_input')?.value || '{{ $deviceName }}' || '';
    const deviceType = document.getElementById('device_type').value;
    const department = document.getElementById('department').value;
    
    let topicName = 'iot';
    
    if (department) {
        topicName += `/${department}`;
    }
    
    if (deviceType) {
        topicName += `/${deviceType}`;
    }
    
    if (deviceName) {
        // Limpar nome do dispositivo (remover espaços e caracteres especiais)
        const cleanName = deviceName.toLowerCase()
            .replace(/[^a-z0-9]/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '');
        topicName += `/${cleanName}`;
    } else {
        topicName += '/dispositivo';
    }
    
    document.getElementById('topicPreview').textContent = topicName;
}

// Carregar MAC address da URL ou localStorage
function loadMacFromSources() {
    console.log('🔍 Verificando MAC address da URL e localStorage...');
    
    // Primeiro: verificar URL
    const urlParams = new URLSearchParams(window.location.search);
    const urlMac = urlParams.get('mac');
    console.log('🌐 MAC encontrado na URL:', urlMac);
    
    // Segundo: verificar localStorage
    const storedMac = localStorage.getItem('esp32_mac_address');
    console.log('📱 MAC encontrado no localStorage:', storedMac);
    
    // Prioridade: URL > localStorage
    const finalMac = urlMac || storedMac;
    console.log('🎯 MAC final selecionado:', finalMac, urlMac ? '(da URL)' : '(do localStorage)');
    
    const detectedSection = document.getElementById('device-detected-section');
    const notDetectedSection = document.getElementById('device-not-detected-section');
    
    if (finalMac) {
        // Se MAC veio da URL, salvar também no localStorage para futuras visitas
        if (urlMac && urlMac !== storedMac) {
            localStorage.setItem('esp32_mac_address', urlMac);
            console.log('💾 MAC da URL salvo no localStorage para futuras visitas');
        }
        
        // Popular campo hidden
        const hiddenMacField = document.querySelector('input[name="mac_address"]');
        if (hiddenMacField) {
            hiddenMacField.value = finalMac;
            console.log('✅ MAC carregado no campo hidden:', finalMac);
        }
        
        // Mostrar seção de dispositivo detectado
        if (detectedSection) {
            const macDisplay = document.getElementById('detected-mac');
            if (macDisplay) {
                macDisplay.textContent = finalMac;
            }
            detectedSection.style.display = 'block';
        }
        
        // Esconder seção de não detectado
        if (notDetectedSection) {
            notDetectedSection.style.display = 'none';
        }
        
        // Limpar URL para deixar mais limpa (opcional)
        if (urlMac) {
            const cleanUrl = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
            console.log('🧹 URL limpa após carregar MAC');
        }
        
    } else {
        console.log('⚠️ Nenhum MAC encontrado na URL nem localStorage');
        
        // Mostrar seção de não detectado
        if (notDetectedSection) {
            notDetectedSection.style.display = 'block';
        }
        
        // Esconder seção de detectado
        if (detectedSection) {
            detectedSection.style.display = 'none';
        }
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Carregar MAC da URL ou localStorage
    loadMacFromSources();
    
    // Atualizar preview inicial
    updateTopicPreview();
    
    // Adicionar listeners aos campos
    const deviceNameInput = document.getElementById('device_name_input');
    if (deviceNameInput) {
        deviceNameInput.addEventListener('input', updateTopicPreview);
    }
    
    document.getElementById('device_type').addEventListener('change', updateTopicPreview);
    document.getElementById('department').addEventListener('change', updateTopicPreview);
});
</script>
@endsection 