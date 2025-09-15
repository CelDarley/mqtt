@extends('layouts.app')

@section('title', 'Conectar ao Dispositivo IoT')

@section('content')
<div class="instructions-container">
    <div class="instructions-card">
        <div class="icon-header">
            <div class="device-icon">📱</div>
            <h1>Conectar ao Dispositivo IoT</h1>
        </div>
        
        <div class="status-warning">
            <div class="warning-icon">⚠️</div>
            <p>Dispositivo IoT não encontrado na rede atual</p>
        </div>
        
        <div class="steps-container">
            <h2>🔧 Siga estes passos para conectar:</h2>
            
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>🔘 Ativar Modo Configuração</h3>
                    <p>Pressione o <strong>botão físico</strong> no dispositivo IoT por 2 segundos. 
                    O LED deve começar a piscar rapidamente.</p>
                    <div class="step-visual">
                        <div class="device-diagram">
                            <div class="device-body"></div>
                            <div class="device-button">BOTÃO</div>
                            <div class="device-led blinking"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>📶 Conectar à Rede WiFi</h3>
                    <p>Vá nas configurações WiFi do seu dispositivo e procure pela rede:</p>
                    <div class="network-info">
                        <div class="network-name">📡 IOT-Zontec</div>
                        <div class="network-password">🔐 Senha: <code>iot123456</code></div>
                    </div>
                    <div class="wifi-steps">
                        <div class="wifi-step">📱 Configurações</div>
                        <div class="arrow">→</div>
                        <div class="wifi-step">📶 WiFi</div>
                        <div class="arrow">→</div>
                        <div class="wifi-step">📡 IOT-Zontec</div>
                    </div>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>✅ Verificar Conexão</h3>
                    <p>Após conectar, você deve ver:</p>
                    <ul class="checklist">
                        <li>✅ WiFi conectado à rede "IOT-Zontec"</li>
                        <li>✅ LED do dispositivo piscando lentamente</li>
                        <li>✅ Seu IP deve ser 192.168.4.X</li>
                    </ul>
                </div>
            </div>
            
            <div class="step final-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>🚀 Tentar Novamente</h3>
                    <p>Quando estiver conectado à rede IOT-Zontec, clique no botão abaixo:</p>
                    <div class="retry-section">
                        <button onclick="checkConnection()" class="retry-button" id="retryButton">
                            <span class="retry-icon">🔄</span>
                            <span class="retry-text">Verificar Conexão</span>
                        </button>
                        <div class="auto-check">
                            <input type="checkbox" id="autoCheck" checked>
                            <label for="autoCheck">Verificar automaticamente a cada 5 segundos</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="help-section">
            <h3>❓ Problemas?</h3>
            <div class="troubleshooting">
                <details>
                    <summary>🔍 Rede IOT-Zontec não aparece</summary>
                    <ul>
                        <li>Verifique se o dispositivo está ligado</li>
                        <li>Pressione o botão novamente por mais tempo</li>
                        <li>Aguarde até 30 segundos para a rede aparecer</li>
                        <li>Verifique se está próximo ao dispositivo</li>
                    </ul>
                </details>
                
                <details>
                    <summary>🔑 Senha não funciona</summary>
                    <ul>
                        <li>Use exatamente: <code>iot123456</code> (tudo em minúsculas)</li>
                        <li>Verifique se não há espaços antes ou depois</li>
                        <li>Tente desconectar e conectar novamente</li>
                    </ul>
                </details>
                
                <details>
                    <summary>⏰ Tempo limite expirou</summary>
                    <ul>
                        <li>O modo configuração dura apenas 5 minutos</li>
                        <li>Pressione o botão novamente para reativar</li>
                        <li>O LED deve voltar a piscar rapidamente</li>
                    </ul>
                </details>
            </div>
        </div>
        
        <div class="navigation-buttons">
            <a href="{{ route('home') }}" class="btn-secondary">
                ⬅️ Voltar ao Dashboard
            </a>
        </div>
    </div>
</div>

<style>
.instructions-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.instructions-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border-radius: 20px;
    padding: 2rem;
    color: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.icon-header {
    text-align: center;
    margin-bottom: 2rem;
}

.device-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.icon-header h1 {
    font-size: 2.2rem;
    margin: 0;
}

.status-warning {
    background: rgba(255, 193, 7, 0.9);
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    margin-bottom: 2rem;
    color: #000;
}

.warning-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.status-warning p {
    margin: 0;
    font-weight: 600;
}

.steps-container h2 {
    text-align: center;
    margin-bottom: 2rem;
    font-size: 1.5rem;
}

.step {
    display: flex;
    margin-bottom: 2rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.step-number {
    background: rgba(255, 255, 255, 0.9);
    color: #f5576c;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
    margin-right: 1.5rem;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.3rem;
}

.step-content p {
    margin: 0 0 1rem 0;
    opacity: 0.9;
    line-height: 1.6;
}

.step-visual {
    text-align: center;
    margin-top: 1rem;
}

.device-diagram {
    position: relative;
    display: inline-block;
    padding: 1rem;
}

.device-body {
    width: 100px;
    height: 60px;
    background: #374151;
    border-radius: 10px;
    position: relative;
    margin: 0 auto;
}

.device-button {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: #ef4444;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: bold;
}

.device-led {
    position: absolute;
    top: 50%;
    right: -20px;
    width: 12px;
    height: 12px;
    background: #10b981;
    border-radius: 50%;
}

.device-led.blinking {
    animation: blink 0.5s infinite alternate;
}

@keyframes blink {
    0% { opacity: 1; }
    100% { opacity: 0.3; }
}

.network-info {
    background: rgba(0, 0, 0, 0.2);
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
}

.network-name {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.network-password {
    font-size: 1rem;
}

.network-password code {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
}

.wifi-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.wifi-step {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

.arrow {
    font-size: 1.2rem;
    font-weight: bold;
}

.checklist {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.checklist li {
    margin: 0.5rem 0;
    padding-left: 0;
}

.final-step {
    border: 2px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.15);
}

.retry-section {
    text-align: center;
    margin-top: 1rem;
}

.retry-button {
    background: linear-gradient(45deg, #10b981, #059669);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
}

.retry-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
}

.retry-button:active {
    transform: translateY(0);
}

.retry-button.checking {
    background: linear-gradient(45deg, #0ea5e9, #0284c7);
    cursor: not-allowed;
}

.retry-icon {
    font-size: 1.2rem;
    animation: spin 2s linear infinite;
}

.retry-button:not(.checking) .retry-icon {
    animation: none;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.auto-check {
    margin-top: 1rem;
    font-size: 0.9rem;
    opacity: 0.8;
}

.auto-check input {
    margin-right: 0.5rem;
}

.help-section {
    background: rgba(255, 255, 255, 0.1);
    padding: 1.5rem;
    border-radius: 10px;
    margin: 2rem 0;
}

.help-section h3 {
    margin-bottom: 1rem;
    color: #fbbf24;
}

.troubleshooting details {
    margin-bottom: 1rem;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    padding: 1rem;
}

.troubleshooting summary {
    cursor: pointer;
    font-weight: 600;
    margin-bottom: 0.5rem;
    list-style: none;
}

.troubleshooting summary::-webkit-details-marker {
    display: none;
}

.troubleshooting summary::before {
    content: "▶️ ";
}

.troubleshooting details[open] summary::before {
    content: "🔽 ";
}

.troubleshooting ul {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
}

.troubleshooting li {
    margin: 0.3rem 0;
    opacity: 0.9;
}

.troubleshooting code {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}

.navigation-buttons {
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
    .instructions-container {
        padding: 1rem;
    }
    
    .instructions-card {
        padding: 1.5rem;
    }
    
    .step {
        flex-direction: column;
        text-align: center;
    }
    
    .step-number {
        margin: 0 auto 1rem auto;
    }
    
    .wifi-steps {
        flex-direction: column;
    }
    
    .arrow {
        transform: rotate(90deg);
    }
}
</style>

<script>
let checkInterval;

document.addEventListener('DOMContentLoaded', function() {
    // Iniciar verificação automática se habilitada
    const autoCheck = document.getElementById('autoCheck');
    if (autoCheck.checked) {
        startAutoCheck();
    }
    
    autoCheck.addEventListener('change', function() {
        if (this.checked) {
            startAutoCheck();
        } else {
            stopAutoCheck();
        }
    });
});

function checkConnection() {
    const button = document.getElementById('retryButton');
    const retryIcon = button.querySelector('.retry-icon');
    const retryText = button.querySelector('.retry-text');
    
    // Mostrar estado de verificação
    button.classList.add('checking');
    retryText.textContent = 'Verificando...';
    
    // Fazer verificação
    fetch('/device/add', {
        method: 'GET',
        headers: {
            'Accept': 'text/html',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            // Se a resposta é OK, provavelmente estamos conectados
            window.location.reload();
        } else {
            throw new Error('Ainda não conectado');
        }
    })
    .catch(error => {
        // Ainda não conectado
        setTimeout(() => {
            button.classList.remove('checking');
            retryText.textContent = 'Verificar Conexão';
        }, 1000);
    });
}

function startAutoCheck() {
    checkInterval = setInterval(() => {
        // Verificação silenciosa
        fetch('/device/add', {
            method: 'GET',
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            }
        })
        .catch(() => {
            // Continuar verificando
        });
    }, 5000);
}

function stopAutoCheck() {
    if (checkInterval) {
        clearInterval(checkInterval);
    }
}
</script>
@endsection 