<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Transição de Rede</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            margin: 20px;
            color: #333;
        }
        .debug-info {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .form-container {
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 20px 0;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background: #007cba;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #005a85;
        }
        .error {
            background: #ffe6e6;
            border: 1px solid #ffb3b3;
            color: #d00;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .success {
            background: #e6ffe6;
            border: 1px solid #b3ffb3;
            color: #080;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>🔧 Debug - Página de Transição</h1>
    
    <div class="debug-info">
        <h3>Informações Recebidas:</h3>
        <p><strong>MAC Address:</strong> {{ $macAddress ?? 'Não informado' }}</p>
        <p><strong>SSID:</strong> {{ $ssid ?? 'Não informado' }}</p>
        <p><strong>URL Atual:</strong> <span id="currentUrl"></span></p>
        <p><strong>Timestamp:</strong> <span id="timestamp"></span></p>
    </div>

    <div class="debug-info">
        <h3>Status de APIs:</h3>
        <p id="apiStatus">🔄 Verificando APIs...</p>
    </div>

    <div class="form-container">
        <h2>📱 Formulário de Configuração do Dispositivo</h2>
        
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
                    <option value="">⏳ Carregando tipos...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="department">Departamento:</label>
                <select id="department" name="department" required>
                    <option value="">⏳ Carregando departamentos...</option>
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
        
        <div id="resultMessage"></div>
    </div>

    <div class="debug-info">
        <h3>Debug Console:</h3>
        <div id="debugConsole" style="background: #f8f8f8; padding: 10px; border: 1px solid #ddd; height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">
        </div>
    </div>

    <script>
        // Debug console
        function debugLog(message) {
            const console = document.getElementById('debugConsole');
            const timestamp = new Date().toLocaleTimeString();
            console.innerHTML += `[${timestamp}] ${message}\n`;
            console.scrollTop = console.scrollHeight;
            console.log(message);
        }

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            debugLog('✅ DOM carregado');
            
            // Mostrar informações básicas
            document.getElementById('currentUrl').textContent = window.location.href;
            document.getElementById('timestamp').textContent = new Date().toLocaleString();
            
            debugLog('📍 URL atual: ' + window.location.href);
            debugLog('🔧 Dados recebidos - MAC: {{ $macAddress }}, SSID: {{ $ssid }}');
            
            // Carregar opções de tipos e departamentos
            loadDeviceOptions();
            
            // Setup do formulário
            setupForm();
        });

        // Carregar opções de tipos e departamentos
        async function loadDeviceOptions() {
            debugLog('🔄 Iniciando carregamento de opções...');
            
            try {
                // Testar APIs internas primeiro
                debugLog('📡 Testando API de tipos...');
                const typesResponse = await fetch('/api/device-types');
                debugLog(`📊 Resposta tipos: ${typesResponse.status} ${typesResponse.statusText}`);
                
                debugLog('📡 Testando API de departamentos...');
                const deptResponse = await fetch('/api/departments');
                debugLog(`📋 Resposta departamentos: ${deptResponse.status} ${deptResponse.statusText}`);
                
                if (!typesResponse.ok || !deptResponse.ok) {
                    throw new Error(`APIs falharam - Tipos: ${typesResponse.status}, Departamentos: ${deptResponse.status}`);
                }
                
                const typesData = await typesResponse.json();
                const deptData = await deptResponse.json();
                
                debugLog(`✅ Dados carregados - Tipos: ${typesData.data?.length || 0}, Departamentos: ${deptData.data?.length || 0}`);
                
                // Popular dropdowns
                populateSelect('device_type', typesData.data, 'Selecione o tipo');
                populateSelect('department', deptData.data, 'Selecione o departamento');
                
                document.getElementById('apiStatus').innerHTML = '✅ APIs funcionando normalmente';
                
            } catch (error) {
                debugLog(`❌ Erro ao carregar opções: ${error.message}`);
                
                // Usar dados de fallback
                const fallbackTypes = [
                    { value: 'sensor', label: '📊 Sensor' },
                    { value: 'atuador', label: '⚡ Atuador' },
                    { value: 'gateway', label: '🌐 Gateway' },
                    { value: 'controlador', label: '🎛️ Controlador' }
                ];
                
                const fallbackDepts = [
                    { value: 'producao', label: '🏭 Produção' },
                    { value: 'qualidade', label: '✅ Qualidade' },
                    { value: 'manutencao', label: '🔧 Manutenção' },
                    { value: 'administrativo', label: '📋 Administrativo' }
                ];
                
                populateSelect('device_type', fallbackTypes, 'Selecione o tipo');
                populateSelect('department', fallbackDepts, 'Selecione o departamento');
                
                document.getElementById('apiStatus').innerHTML = '⚠️ Usando dados de fallback devido a erro nas APIs';
                debugLog('⚠️ Usando dados de fallback');
            }
        }

        // Popular select com opções
        function populateSelect(selectId, data, placeholder) {
            const select = document.getElementById(selectId);
            select.innerHTML = `<option value="">${placeholder}</option>`;
            
            if (data && Array.isArray(data)) {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.value;
                    option.textContent = item.label;
                    select.appendChild(option);
                });
                debugLog(`✅ Select ${selectId} populado com ${data.length} itens`);
            } else {
                debugLog(`❌ Dados inválidos para select ${selectId}:`, data);
            }
        }

        // Setup do formulário
        function setupForm() {
            const form = document.getElementById('deviceConfigForm');
            
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                debugLog('📤 Enviando formulário...');
                
                const submitBtn = e.target.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                
                submitBtn.textContent = '⏳ Enviando...';
                submitBtn.disabled = true;
                
                try {
                    const formData = new FormData(e.target);
                    
                    // Debug dos dados do formulário
                    debugLog('📋 Dados do formulário:');
                    for (let [key, value] of formData.entries()) {
                        debugLog(`  ${key}: ${value}`);
                    }
                    
                    const response = await fetch('/device/save-topic', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    debugLog(`📨 Resposta do servidor: ${response.status} ${response.statusText}`);
                    
                    const result = await response.json();
                    debugLog(`📄 Dados da resposta: ${JSON.stringify(result, null, 2)}`);
                    
                    if (result.success) {
                        showResult('success', `✅ ${result.message}`);
                        debugLog('✅ Tópico criado com sucesso');
                    } else {
                        showResult('error', `❌ ${result.message}`);
                        debugLog('❌ Erro ao criar tópico');
                    }
                    
                } catch (error) {
                    debugLog(`❌ Erro de rede: ${error.message}`);
                    showResult('error', `❌ Erro de conexão: ${error.message}`);
                } finally {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            });
            
            debugLog('✅ Formulário configurado');
        }

        // Mostrar resultado
        function showResult(type, message) {
            const resultDiv = document.getElementById('resultMessage');
            resultDiv.className = type;
            resultDiv.innerHTML = message;
        }

        // Log inicial
        debugLog('🚀 Script iniciado');
    </script>
</body>
</html> 