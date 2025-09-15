# 🚦 Funcionamento dos LEDs no ESP32

## 📌 Pinos Utilizados

- **LED_PIN (48)**: LED interno do ESP32-S3-WROOM
- **LED_EXTERNAL_PIN (16)**: LED externo no GPIO16 - **STATUS DE CONEXÃO**
- **LED_MQTT_PIN (19)**: LED no GPIO19 - **NOTIFICAÇÕES MQTT**

## 🔵 LED de Status de Conexão (Pinos 48 + 16)

Estes LEDs trabalham em conjunto para indicar o status da conexão WiFi:

### Estados:

| Estado | Comportamento | Significado |
|--------|---------------|-------------|
| **DESLIGADO** | LEDs apagados | Dispositivo iniciando |
| **PISCAR RÁPIDO** | Pisca a cada 200ms | Modo AP ativo (aguardando configuração) OU erro de conexão |
| **PISCAR LENTO** | Pisca a cada 1000ms | Tentando conectar ao WiFi |
| **LIGADO FIXO** | LEDs sempre acesos | Conectado ao WiFi com sucesso |

### Fluxo de Estados:

1. **Início**: LEDs desligados
2. **Carregamento**: Se credenciais salvas → Piscar lento
3. **Conexão bem-sucedida**: Ligado fixo
4. **Falha na conexão**: Piscar rápido (2s) → Modo AP (piscar rápido)
5. **Modo AP**: Piscar rápido (aguardando configuração)
6. **Configuração**: Piscar lento (tentando conectar)
7. **Sucesso**: Ligado fixo
8. **Falha**: Volta para piscar rápido (modo AP)

## 🟡 LED de Notificações MQTT (Pino 19)

Este LED é usado exclusivamente para feedback de operações MQTT:

### Estados:

| Comportamento | Significado |
|---------------|-------------|
| **DESLIGADO** | Normal (sem atividade MQTT) |
| **3 PISCADAS LENTAS** | Dispositivo registrado com sucesso (novo) |
| **2 PISCADAS LENTAS** | Dispositivo já registrado e ativado |
| **1 PISCADA LONGA** | Dispositivo registrado mas aguardando ativação |
| **5 PISCADAS RÁPIDAS** | Erro HTTP no registro |
| **10 PISCADAS MUITO RÁPIDAS** | Erro de conexão de rede |
| **PISCAR CONTÍNUO** | Mensagem MQTT recebida (implementação futura) |

### Detalhes das Notificações:

- **Registro novo**: 3 piscadas de 200ms com intervalo de 200ms
- **Já registrado e ativo**: 2 piscadas de 300ms com intervalo de 300ms
- **Aguardando ativação**: 1 piscada de 1000ms
- **Erro HTTP**: 5 piscadas de 100ms com intervalo de 100ms
- **Erro de rede**: 10 piscadas de 50ms com intervalo de 50ms
- Após qualquer notificação, o LED volta ao estado desligado

## 🔧 Implementação Técnica

### Funções Principais:

- `setLedState(int state)`: Define o estado dos LEDs de conexão
- `updateLed()`: Atualiza o piscar dos LEDs (chamada no loop)
- `digitalWrite(LED_MQTT_PIN, HIGH/LOW)`: Controla LED MQTT diretamente

### Estados dos LEDs de Conexão:

```cpp
#define LED_OFF 0          // Desligado
#define LED_ON 1           // Ligado fixo
#define LED_FAST_BLINK 2   // Piscar rápido (200ms)
#define LED_SLOW_BLINK 3   // Piscar lento (1000ms)
```

## 🚨 Solução de Problemas

### LED de conexão não funciona:
- Verificar se `updateLed()` está sendo chamado no `loop()`
- Verificar se `setLedState()` está sendo chamado nos momentos corretos
- Verificar conexões físicas dos LEDs

### LED MQTT piscando continuamente:
- Verificar se não há loop infinito chamando `digitalWrite(LED_MQTT_PIN, HIGH)`
- Verificar se `digitalWrite(LED_MQTT_PIN, LOW)` é chamado após notificações
- Reiniciar o dispositivo para limpar estado inconsistente

### LED não indica status correto:
- Verificar se `WiFi.status()` retorna o valor esperado
- Verificar lógica de monitoramento de conexão no `loop()`
- Verificar se credenciais WiFi estão corretas

## 📋 Monitoramento

Para depuração, monitor serial mostra:
- Status de conexão WiFi
- Mudanças de estado dos LEDs
- Resultados de registro no backend
- Erros de conexão

Use o Serial Monitor a 115200 baud para acompanhar o comportamento. 