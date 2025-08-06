# 🚀 Endpoints Rápidos - API MQTT

## 📋 Base URL
```
http://10.102.0.21:8000
```

---

## 📤 1. LISTAR TÓPICOS
```
GET http://10.102.0.21:8000/api/mqtt/topics
```

---

## ➕ 2. CRIAR TÓPICO
```
POST http://10.102.0.21:8000/api/mqtt/topics
Content-Type: application/json

{
  "name": "dispositivo/porta",
  "description": "Controle da porta principal"
}
```

---

## 👁️ 3. MOSTRAR TÓPICO
```
GET http://10.102.0.21:8000/api/mqtt/topics/1
```

---

## 🚫 4. DESATIVAR TÓPICO
```
PATCH http://10.102.0.21:8000/api/mqtt/topics/1/deactivate
```

---

## 📤 5. ENVIAR MENSAGEM
```
POST http://10.102.0.21:8000/api/mqtt/send-message
Content-Type: application/json

{
  "topico": "dispositivo/porta",
  "mensagem": "liberar"
}
```

---

## 🎯 EXEMPLOS PRÁTICOS

### Criar e Usar LED
```bash
# Criar tópico
curl -X POST http://10.102.0.21:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{"name": "dispositivo/led", "description": "Controle do LED"}'

# Ligar LED
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "dispositivo/led", "mensagem": "ligar"}'

# Desligar LED
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "dispositivo/led", "mensagem": "desligar"}'
```

### Controle de Porta
```bash
# Criar tópico
curl -X POST http://10.102.0.21:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{"name": "dispositivo/porta", "description": "Controle da porta"}'

# Liberar porta
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "dispositivo/porta", "mensagem": "liberar"}'

# Bloquear porta
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "dispositivo/porta", "mensagem": "bloquear"}'
```

---

## 📊 CÓDIGOS DE STATUS
- `200` ✅ Sucesso
- `201` ✅ Criado
- `400` ❌ Erro de validação
- `404` ❌ Não encontrado
- `500` ❌ Erro interno

---

## 🔧 IMPORTAR NO POSTMAN
1. Baixe: `postman_collection.json`
2. Abra Postman
3. Import → Selecione o arquivo
4. Pronto! 🎉

---

## 🧪 TESTE RÁPIDO
```bash
./teste_api_ip.sh
``` 