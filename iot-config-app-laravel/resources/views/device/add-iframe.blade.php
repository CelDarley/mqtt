@extends('layouts.app')

@section('title', 'Configurar Dispositivo IoT')

@section('content')
<div class="config-container">
    <div class="device-card">
        <h1>🔧 Configuração do Dispositivo IoT</h1>
        <p>Complete a configuração do seu dispositivo IoT através da interface integrada abaixo.</p>
        
        <div class="iframe-container">
            <div class="iframe-header">
                <div class="status-indicator">
                    <span class="status-dot connected"></span>
                    <span>Conectado ao dispositivo IoT</span>
                </div>
                <div class="device-info">
                    <span>📡 IOT-Zontec • 192.168.4.1</span>
                </div>
            </div>
            
            <iframe 
                id="deviceIframe"
                src="http://192.168.4.1:5000/config"
                frameborder="0"
                sandbox="allow-scripts allow-forms allow-same-origin allow-popups allow-popups-to-escape-sandbox"
                loading="lazy">
                <p>Seu navegador não suporta iframes. <a href="http://192.168.4.1:5000/config" target="_blank">Clique aqui para abrir em nova aba</a></p>
            </iframe>
            
            <div class="iframe-loading" id="iframeLoading">
                <div class="loading-spinner"></div>
                <p>Carregando interface do dispositivo...</p>
            </div>
            
            <div class="iframe-error" id="iframeError" style="display: none;">
                <div class="error-icon">⚠️</div>
                <h3>Erro de Conexão</h3>
                <p>Não foi possível carregar a interface do dispositivo.</p>
                <button onclick="reloadIframe()" class="retry-button">🔄 Tentar Novamente</button>
            </div>
        </div>
        
        <div class="help-section">
            <h3>ℹ️ Como usar:</h3>
            <ol>
                <li>Preencha todos os campos no formulário acima</li>
                <li>Insira as credenciais da sua rede WiFi</li>
                <li>Clique em "Configurar Dispositivo"</li>
                <li>Aguarde a confirmação de sucesso</li>
            </ol>
        </div>
        
        <div class="navigation-buttons">
            <a href="{{ route('home') }}" class="btn-secondary">
                ⬅️ Voltar ao Dashboard
            </a>
        </div>
    </div>
</div>

<style>
.config-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem;
}

.device-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 2rem;
    color: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.device-card h1 {
    text-align: center;
    margin-bottom: 1rem;
    font-size: 2rem;
}

.device-card p {
    text-align: center;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.iframe-container {
    position: relative;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.iframe-header {
    background: rgba(0, 0, 0, 0.2);
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

.status-dot.connected {
    background: #10b981;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.device-info {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    opacity: 0.8;
}

#deviceIframe {
    width: 100%;
    height: 600px;
    border: none;
    background: white;
}

.iframe-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 10;
}

.iframe-error {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 15;
    background: rgba(239, 68, 68, 0.9);
    padding: 2rem;
    border-radius: 10px;
    color: white;
}

.error-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.retry-button {
    background: white;
    color: #ef4444;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    margin-top: 1rem;
}

.retry-button:hover {
    background: #f1f5f9;
}

.direct-link-button {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 1rem 2rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    margin: 1rem 0;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    transition: all 0.3s ease;
}

.direct-link-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
    color: white;
    text-decoration: none;
}

.help-section {
    background: rgba(255, 255, 255, 0.1);
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.help-section h3 {
    margin-bottom: 1rem;
    color: #fbbf24;
}

.help-section ol {
    margin: 0;
    padding-left: 1.5rem;
}

.help-section li {
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.navigation-buttons {
    text-align: center;
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
    .config-container {
        padding: 1rem;
    }
    
    .device-card {
        padding: 1.5rem;
    }
    
    .iframe-header {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    
    #deviceIframe {
        height: 500px;
    }
}
</style>

<script>
// Gerenciar carregamento do iframe
document.addEventListener('DOMContentLoaded', function() {
    const iframe = document.getElementById('deviceIframe');
    const loading = document.getElementById('iframeLoading');
    const error = document.getElementById('iframeError');
    
    let loaded = false;
    
    // Timeout para detectar erro de carregamento
    const loadTimeout = setTimeout(() => {
        if (!loaded) {
            console.log('⏰ Timeout ao carregar iframe - mostrando botão para abrir diretamente');
            loading.style.display = 'none';
            showDirectLinkOption();
        }
    }, 8000); // 8 segundos
    
    iframe.onload = function() {
        loaded = true;
        clearTimeout(loadTimeout);
        loading.style.display = 'none';
        iframe.style.display = 'block';
        console.log('✅ Iframe carregado com sucesso');
    };
    
    iframe.onerror = function() {
        loaded = true;
        clearTimeout(loadTimeout);
        loading.style.display = 'none';
        showDirectLinkOption();
        console.log('❌ Erro ao carregar iframe');
    };
    
    // Verificar se consegue conectar ao dispositivo
    checkDeviceConnection();
});

function showDirectLinkOption() {
    const error = document.getElementById('iframeError');
    error.innerHTML = `
        <div class="error-icon">🔗</div>
        <h3>Interface Não Carregou</h3>
        <p>A interface integrada não carregou. Use o link direto abaixo:</p>
        <a href="http://192.168.4.1:5000/config" target="_blank" class="direct-link-button">
            🚀 Abrir Interface do Dispositivo
        </a>
        <br><br>
        <button onclick="reloadIframe()" class="retry-button">🔄 Tentar Iframe Novamente</button>
    `;
    error.style.display = 'block';
}

async function checkDeviceConnection() {
    try {
        console.log('🔍 Verificando conectividade com dispositivo...');
        const response = await fetch('http://192.168.4.1:5000/api/status', { 
            mode: 'no-cors',
            timeout: 5000 
        });
        console.log('✅ Dispositivo acessível');
    } catch (error) {
        console.log('❌ Dispositivo não acessível:', error.message);
        setTimeout(() => {
            if (!document.getElementById('deviceIframe').style.display) {
                showDirectLinkOption();
            }
        }, 3000);
    }
}

function reloadIframe() {
    const iframe = document.getElementById('deviceIframe');
    const loading = document.getElementById('iframeLoading');
    const error = document.getElementById('iframeError');
    
    error.style.display = 'none';
    loading.style.display = 'block';
    iframe.src = iframe.src; // Recarregar
}

// Monitorar mensagens do iframe para feedback
window.addEventListener('message', function(event) {
    // Verificar origem por segurança
    if (event.origin !== 'http://192.168.4.1:5000') {
        return;
    }
    
    // Processar mensagens do dispositivo
    if (event.data.type === 'device_configured') {
        // Dispositivo foi configurado com sucesso
        setTimeout(() => {
            alert('🎉 Dispositivo configurado com sucesso! Você pode agora voltar à sua rede WiFi principal.');
        }, 1000);
    }
});
</script>
@endsection 