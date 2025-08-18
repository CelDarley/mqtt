# GPIO Raspberry Pi - Vista Correta

## Vista da Placa (Componentes para cima, USB/Ethernet embaixo)

```
                    RASPBERRY PI
                    ============

    ┌─────────────────────────────────────────────────────────┐
    │                                                       │
    │  ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ │
    │  │1│ │3│ │5│ │7│ │9│ │11│ │13│ │15│ │17│ │19│ │21│ │23│ │25│ │27│ │29│ │31│ │33│ │35│ │37│ │39│ │
    │  └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ │
    │  ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ │
    │  │2│ │4│ │6│ │8│ │10│ │12│ │14│ │16│ │18│ │20│ │22│ │24│ │26│ │28│ │30│ │32│ │34│ │36│ │38│ │40│ │
    │  └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ │
    │                                                       │
    └─────────────────────────────────────────────────────────┘
                                    │
                                    │ USB/Ethernet embaixo
                                    ▼
```

## Contagem dos Pinoss Pares (Linha da Direita)

### Sua contagem:
```
1º pino = Pino 2   (5V)
2º pino = Pino 4   (5V)
3º pino = Pino 6   (GND)
4º pino = Pino 8   (GPIO14 - TXD)
5º pino = Pino 10  (GPIO15 - RXD)
6º pino = Pino 12  (GPIO18)
7º pino = Pino 14  (GND) ← SEU LED NEGATIVO AQUI
8º pino = Pino 16  (GPIO23) ← SEU LED POSITIVO AQUI
9º pino = Pino 18  (GPIO24)
10º pino = Pino 20 (GND)
```

## Sua Conexão Atual (Corrigida)

Baseado na sua contagem:
- **LED - (Negativo)** → **Pino 14** (GND) → Resistor → **Pino 16** (GPIO23)

### Problema Identificado:
Você conectou o LED entre os pinos 14 e 16, mas:
- **Pino 14** = GND (Terra) ✅
- **Pino 16** = GPIO23 (Saída) ✅

**Esta conexão está CORRETA!** 

## Teste da Conexão

### 1. Verificar se o pino 16 está funcionando:
```bash
# Testar pino 16 (GPIO23)
echo "23" > /sys/class/gpio/export
echo "out" > /sys/class/gpio/gpio23/direction
echo "1" > /sys/class/gpio/gpio23/value  # Ligar
echo "0" > /sys/class/gpio/gpio23/value  # Desligar
```

### 2. Testar com Python:
```bash
python3 teste_pino16.py
```

## Possíveis Problemas

### 1. **Polaridade do LED**
- Terminal longo = Positivo (+)
- Terminal curto = Negativo (-)
- Verifique se está conectado corretamente

### 2. **Resistor**
- Use um resistor de 220Ω
- Conecte entre o LED negativo e o GND

### 3. **Conexões**
- Verifique se as conexões estão firmes
- Teste com multímetro se necessário

### 4. **Permissões GPIO**
```bash
# Verificar se o usuário tem permissão
sudo usermod -a -G gpio $USER
# Reiniciar ou fazer logout/login
```

## Conexão Correta Final

```
LED + (Positivo/Longo) → Pino 16 (GPIO23)
LED - (Negativo/Curto) → Resistor 220Ω → Pino 14 (GND)
```

## Teste Rápido

Execute este comando para testar:
```bash
python3 teste_pino16.py
```

Se ainda não funcionar, tente:
```bash
# Testar outros pinos
python3 teste_led.py  # Testa pino 16
```

A conexão que você fez está correta! O problema pode ser:
1. Polaridade do LED
2. Resistor não conectado
3. Conexões soltas
4. Permissões GPIO 