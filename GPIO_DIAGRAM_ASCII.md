# Diagrama ASCII do GPIO Raspberry Pi

```
                    RASPBERRY PI GPIO HEADER
                    =========================

    Vista Superior (Pinos numerados)
    ┌─────────────────────────────────────────────────────────┐
    │                                                       │
    │  ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ │
    │  │1│ │3│ │5│ │7│ │9│ │11│ │13│ │15│ │17│ │19│ │21│ │23│ │25│ │27│ │29│ │31│ │33│ │35│ │37│ │39│ │
    │  └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ │
    │  ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ │
    │  │2│ │4│ │6│ │8│ │10│ │12│ │14│ │16│ │18│ │20│ │22│ │24│ │26│ │28│ │30│ │32│ │34│ │36│ │38│ │40│ │
    │  └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ │
    │                                                       │
    └─────────────────────────────────────────────────────────┘

    LEGENDA DETALHADA:
    ┌─────────────────────────────────────────────────────────┐
    │ PINO 1  = 3.3V (Alimentação)                         │
    │ PINO 2  = 5V (Alimentação)                           │
    │ PINO 6  = GND (Terra) ← CONECTAR AQUI                │
    │ PINO 16 = GPIO23 (BCM) ← CONECTAR AQUI               │
    │ PINO 8  = GND (Terra)                                │
    │ PINO 10 = GND (Terra)                                │
    │ PINO 14 = GND (Terra)                                │
    │ PINO 17 = 3.3V (Alimentação)                         │
    │ PINO 20 = GND (Terra)                                │
    │ PINO 25 = GND (Terra)                                │
    │ PINO 30 = GND (Terra)                                │
    │ PINO 34 = GND (Terra)                                │
    │ PINO 39 = GND (Terra)                                │
    └─────────────────────────────────────────────────────────┘
```

## Conexão LED - Diagrama Detalhado

```
                    CONEXÃO LED NO PINO 16
                    ======================

    ┌─────────────────────────────────────────────────────────┐
    │                    RASPBERRY PI                       │
    │                                                       │
    │  ┌─────────────────────────────────────────────────┐   │
    │  │              GPIO HEADER                       │   │
    │  │                                               │   │
    │  │  ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ │   │
    │  │  │1│ │3│ │5│ │7│ │9│ │11│ │13│ │15│ │17│ │19│ │   │
    │  │  └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ │   │
    │  │  ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ │   │
    │  │  │2│ │4│ │6│ │8│ │10│ │12│ │14│ │16│ │18│ │20│ │   │
    │  │  └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ └─┘ │   │
    │  │                                               │   │
    │  └─────────────────────────────────────────────────┘   │
    │                                                       │
    └─────────────────────────────────────────────────────────┘
                              │
                              │ FIO VERMELHO
                              ▼
                          ┌─────────┐
                          │   LED   │
                          │  ┌─┐    │
                          │  │ │    │
                          │  └─┘    │
                          └─────────┘
                              │
                              │ FIO PRETO
                              ▼
                          ┌─────────┐
                          │ RESISTOR│
                          │  220Ω   │
                          └─────────┘
                              │
                              ▼
                          ┌─────────┐
                          │   GND   │
                          │ (Pino 6)│
                          └─────────┘
```

## Instruções Passo a Passo

### 1. Identificar os Pinoss Corretos
```
PINO 16 (GPIO23) = Segunda linha, oitava posição
PINO 6  (GND)    = Primeira linha, terceira posição
```

### 2. Conexão do LED
```
LED + (Anodo) → Pino 16 (GPIO23)
LED - (Catodo) → Resistor 220Ω → Pino 6 (GND)
```

### 3. Verificação Visual
- **Pino 16**: Segunda linha, oitava posição da esquerda
- **Pino 6**: Primeira linha, terceira posição da esquerda
- **Pino 1**: Primeira linha, primeira posição (3.3V)

### 4. Teste de Conexão
1. Conecte o LED ao pino 16 (GPIO23)
2. Conecte o resistor 220Ω ao terminal negativo do LED
3. Conecte o resistor ao pino 6 (GND)
4. Execute o teste: `python3 teste_led.py`

## Dicas Importantes

⚠️ **VERIFICAÇÕES:**
- O LED tem polaridade: terminal longo (+) e curto (-)
- Use sempre um resistor (220Ω) para proteger o LED
- Verifique se as conexões estão firmes
- Teste com multímetro se necessário
- Certifique-se de que o Raspberry Pi está ligado 