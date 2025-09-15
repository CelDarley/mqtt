<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração do Dispositivo - IoT Config</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 20px;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        
        .device-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: left;
        }
        
        .device-info .label {
            font-weight: 500;
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .device-info .value {
            font-family: monospace;
            font-size: 1.1rem;
            margin-top: 0.2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            font-size: 1rem;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }
        
        .btn {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .info-text {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 1rem;
        }
        
        .error-message {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid rgba(244, 67, 54, 0.5);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .success-message {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.5);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">📱</div>
        <div class="title">Configuração do Dispositivo IoT</div>
            
            <div class="device-info">
                <div class="label">MAC Address do ESP32:</div>
                <div class="value">{{ $macAddress ?? 'N/A' }}</div>
            </div>
            
            <div class="device-info">
                <div class="label">Rede WiFi de Destino:</div>
                <div class="value">{{ $ssid ?? 'N/A' }}</div>
            </div>
            
            <form id="deviceConfigForm" action="{{ route('device.save-topic') }}" method="POST">
                @csrf
                
                <input type="hidden" name="mac_address" value="{{ $macAddress }}">
                <input type="hidden" name="ssid" value="{{ $ssid }}">
                
                <div class="form-group">
                    <label for="device_name">Nome do Dispositivo:</label>
                    <input type="text" id="device_name" name="device_name" required 
                           placeholder="Ex: Sensor Temperatura">
                </div>
                
                                 <div class="form-group">
                     <label for="device_type">Tipo de Dispositivo:</label>
                     <select id="device_type" name="device_type" required>
                    <option value="">Selecione o tipo</option>
                    <option value="sensor">📊 Sensor</option>
                    <option value="atuador">⚡ Atuador</option>
                    <option value="gateway">🌐 Gateway</option>
                    <option value="controlador">🎛️ Controlador</option>
                     </select>
                 </div>
                 
                 <div class="form-group">
                     <label for="department">Departamento:</label>
                     <select id="department" name="department" required>
                    <option value="">Selecione o departamento</option>
                    <option value="producao">🏭 Produção</option>
                    <option value="qualidade">✅ Qualidade</option>
                    <option value="manutencao">🔧 Manutenção</option>
                    <option value="administrativo">📋 Administrativo</option>
                     </select>
                 </div>
                
                <div class="form-group">
                    <label for="description">Descrição (opcional):</label>
                    <input type="text" id="description" name="description" 
                           placeholder="Descrição adicional do dispositivo">
                </div>
                
                <button type="submit" class="btn">
                    ✅ Criar Tópico MQTT
                </button>
            </form>
            
            <div class="info-text">
                💡 Após criar o tópico, o dispositivo estará pronto para comunicação MQTT
        </div>
    </div>

    <script>
        console.log('🔧 Página de transição carregada');
        console.log('Dados recebidos:', {
            macAddress: '{{ $macAddress ?? "N/A" }}',
            ssid: '{{ $ssid ?? "N/A" }}'
        });

        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ DOM carregado');
            
            // Focar no primeiro campo
            const deviceNameField = document.getElementById('device_name');
            if (deviceNameField) {
                deviceNameField.focus();
            }
            
            // Setup do formulário
            setupForm();
        });

        function setupForm() {
            const form = document.getElementById('deviceConfigForm');
            
            form.addEventListener('submit', async function(e) {
             e.preventDefault();
             
             const submitBtn = e.target.querySelector('button[type="submit"]');
             const originalText = submitBtn.textContent;
             
             // Desabilitar botão e mostrar loading
                submitBtn.textContent = '⏳ Criando tópico...';
             submitBtn.disabled = true;
                
                // Remover mensagens anteriores
                removeMessages();
             
             try {
                 const formData = new FormData(e.target);
                    
                    console.log('📤 Enviando formulário...');
                    console.log('Dados:', Object.fromEntries(formData));
                 
                 const response = await fetch('/device/save-topic', {
                     method: 'POST',
                     body: formData,
                     headers: {
                         'X-Requested-With': 'XMLHttpRequest'
                     }
                 });
                    
                    console.log(`📨 Resposta: ${response.status} ${response.statusText}`);
                 
                 const result = await response.json();
                    console.log('📄 Resultado:', result);
                 
                 if (result.success) {
                        showSuccess(result.message);
                        console.log('✅ Tópico criado com sucesso');
                        
                        // Limpar formulário após sucesso
                        setTimeout(() => {
                            form.reset();
                        }, 2000);
                        
                 } else {
                        showError(result.message || 'Erro desconhecido');
                        console.error('❌ Erro ao criar tópico:', result);
                 }
                 
             } catch (error) {
                    console.error('❌ Erro de rede:', error);
                    showError('Erro de conexão com o servidor. Verifique sua conexão e tente novamente.');
             } finally {
                 // Reabilitar botão
                 submitBtn.textContent = originalText;
                 submitBtn.disabled = false;
             }
         });
         
            console.log('✅ Formulário configurado');
        }

        function showError(message) {
            const container = document.querySelector('.container');
             const errorDiv = document.createElement('div');
             errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<strong>❌ ${message}</strong>`;
            
            // Inserir antes do formulário
            const form = document.getElementById('deviceConfigForm');
            container.insertBefore(errorDiv, form);
        }

        function showSuccess(message) {
            const container = document.querySelector('.container');
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `<strong>✅ ${message}</strong>`;
            
            // Inserir antes do formulário
            const form = document.getElementById('deviceConfigForm');
            container.insertBefore(successDiv, form);
        }

        function removeMessages() {
            const errorMessages = document.querySelectorAll('.error-message, .success-message');
            errorMessages.forEach(msg => msg.remove());
        }
    </script>
</body>
</html> 