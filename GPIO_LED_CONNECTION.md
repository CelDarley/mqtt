# GPIO Raspberry Pi - Conexão LED Pino 12

## Diagrama do GPIO (Vista Superior)

```
    Raspberry Pi GPIO Header
    ========================

    Pinos Ímpares (1-39)        Pinos Pares (2-40)
    ┌─────────────────────────┐  ┌─────────────────────────┐
    │ 1  3  5  7  9  11 13 15│  │ 2  4  6  8  10 12 14 16│
    │17 19 21 23 25 27 29 31│  │18 20 22 24 26 28 30 32│
    │33 35 37 39             │  │34 36 38 40             │
    └─────────────────────────┘  └─────────────────────────┘

    LEGENDA:
    ┌─────────────────────────────────────────────────────────┐
    │ 3.3V  = Alimentação 3.3V                              │
    │ 5V    = Alimentação 5V                                │
    │ GND   = Terra (Ground)                                │
    │ GPIO  = Pino de entrada/saída programável             │
    │ ID_SD = Identificação do cartão SD                    │
    │ ID_SC = Clock de identificação                        │
    └─────────────────────────────────────────────────────────┘
```

## Conexão Específica para LED no Pino 16 (GPIO23)

```
    CONEXÃO LED - PINO 16 (GPIO23)
    ===============================

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
                              │ FIO VERMELHO (LED +)
                              ▼
                          ┌─────────┐
                          │   LED   │
                          │  ┌─┐    │
                          │  │ │    │
                          │  └─┘    │
                          └─────────┘
                              │
                              │ FIO PRETO (LED -)
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

## Detalhamento dos Pinoss

### Pinos de Alimentação (3.3V e 5V)
```
Pino 1  = 3.3V
Pino 2  = 5V
Pino 4  = 5V
Pino 6  = GND (Terra)
Pino 8  = GND (Terra)
Pino 9  = GND (Terra)
Pino 14 = GND (Terra)
Pino 17 = 3.3V
Pino 20 = GND (Terra)
Pino 25 = GND (Terra)
Pino 30 = GND (Terra)
Pino 34 = GND (Terra)
Pino 39 = GND (Terra)
```

### Pinos GPIO Importantes
```
Pino 16 = GPIO23 (BCM) - LED CONTROL
Pino 6  = GND         - TERRA
Pino 1  = 3.3V        - ALIMENTAÇÃO
```

## Instruções de Conexão

### 1. Identificar os Pinoss
- **Pino 16** (GPIO23): Localizado na segunda linha, oitava posição
- **Pino 6** (GND): Localizado na primeira linha, terceira posição
- **Pino 1** (3.3V): Localizado na primeira linha, primeira posição

### 2. Conexão do LED
```
LED + (Anodo/Longo) → Pino 16 (GPIO23)
LED - (Catodo/Curto) → Resistor 220Ω → Pino 6 (GND)
```

### 3. Diagrama de Conexão Simplificado
```
Raspberry Pi
┌─────────────┐
│             │
│  Pino 16    │◄─── LED + (Vermelho)
│  (GPIO23)   │
│             │
│  Pino 6     │◄─── Resistor 220Ω ◄─── LED - (Preto)
│  (GND)      │
│             │
└─────────────┘
```

## Verificação Visual

### Como Identificar os Pinoss:
1. **Pino 1**: Primeira posição, primeira linha (3.3V)
2. **Pino 6**: Terceira posição, primeira linha (GND)
3. **Pino 16**: Oitava posição, segunda linha (GPIO23)

### Teste de Continuidade:
- Use um multímetro para verificar se as conexões estão corretas
- Teste entre o pino 16 e o terminal positivo do LED
- Teste entre o pino 6 e o terminal negativo do LED

## Notas Importantes

⚠️ **ATENÇÃO:**
- Sempre use um resistor (220Ω) para proteger o LED
- O LED tem polaridade: terminal longo (+) e curto (-)
- Conecte sempre o GND (terra) para completar o circuito
- Verifique se o Raspberry Pi está desligado antes de conectar 