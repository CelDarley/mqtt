# 🚀 Endpoints da API MQTT - Postman

## 📋 Informações Gerais

- **Base URL**: `http://10.102.0.21:8000`
- **IP**: 10.102.0.21
- **Porta**: 8000
- **Usuário**: darley
- **Senha**: yhvh77

---

## 📤 1. Listar Tópicos

### **GET** `/api/mqtt/topics`

**Headers:**
```
Accept: application/json
```

**URL Completa:**
```
http://10.102.0.21:8000/api/mqtt/topics
```

**Resposta de Exemplo:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "dispositivo/porta",
      "description": "Controle da porta principal",
      "is_active": true,
      "created_at": "2025-08-05T23:16:13.000000Z",
      "updated_at": "2025-08-05T23:16:13.000000Z"
    }
  ]
}
```

---

## ➕ 2. Criar Tópico

### **POST** `/api/mqtt/topics`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "name": "dispositivo/porta",
  "description": "Controle da porta principal"
}
```

**URL Completa:**
```
http://10.102.0.21:8000/api/mqtt/topics
```

**Resposta de Exemplo:**
```json
{
  "success": true,
  "message": "Tópico criado com sucesso",
  "data": {
    "id": 1,
    "name": "dispositivo/porta",
    "description": "Controle da porta principal",
    "is_active": true,
    "created_at": "2025-08-05T23:16:13.000000Z",
    "updated_at": "2025-08-05T23:16:13.000000Z"
  }
}
```

---

## 👁️ 3. Mostrar Tópico Específico

### **GET** `/api/mqtt/topics/{id}`

**Headers:**
```
Accept: application/json
```

**URL Completa:**
```
http://10.102.0.21:8000/api/mqtt/topics/1
```

**Resposta de Exemplo:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "dispositivo/porta",
    "description": "Controle da porta principal",
    "is_active": true,
    "created_at": "2025-08-05T23:16:13.000000Z",
    "updated_at": "2025-08-05T23:16:13.000000Z"
  }
}
```

---

## 🚫 4. Desativar Tópico

### **PATCH** `/api/mqtt/topics/{id}/deactivate`

**Headers:**
```
Accept: application/json
```

**URL Completa:**
```
http://10.102.0.21:8000/api/mqtt/topics/1/deactivate
```

**Resposta de Exemplo:**
```json
{
  "success": true,
  "message": "Tópico desativado com sucesso",
  "data": {
    "id": 1,
    "name": "dispositivo/porta",
    "is_active": false
  }
}
```

---

## 📤 5. Enviar Mensagem

### **POST** `/api/mqtt/send-message`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "topico": "dispositivo/porta",
  "mensagem": "liberar"
}
```

**URL Completa:**
```
http://10.102.0.21:8000/api/mqtt/send-message
```

**Resposta de Exemplo:**
```json
{
  "success": true,
  "message": "Mensagem enviada com sucesso para o tópico: dispositivo/porta",
  "data": {
    "topic": "dispositivo/porta",
    "message": "liberar"
  }
}
```

---

## 🔒 6. Teste - Tópico Inexistente

### **POST** `/api/mqtt/send-message`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "topico": "topico/inexistente",
  "mensagem": "liberar"
}
```

**URL Completa:**
```
http://10.102.0.21:8000/api/mqtt/send-message
```

**Resposta de Exemplo:**
```json
{
  "success": false,
  "message": "Tópico não existe ou está inativo"
}
```

---

## 🎯 Exemplos de Uso

### Exemplo 1: Criar e Usar um Tópico
```bash
# 1. Criar tópico
curl -X POST http://10.102.0.21:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{"name": "sensor/temperatura", "description": "Sensor de temperatura"}'

# 2. Enviar mensagem
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "sensor/temperatura", "mensagem": "25.5"}'
```

### Exemplo 2: Controle de Dispositivo
```bash
# 1. Criar tópico para LED
curl -X POST http://10.102.0.21:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{"name": "dispositivo/led", "description": "Controle do LED"}'

# 2. Ligar LED
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "dispositivo/led", "mensagem": "ligar"}'

# 3. Desligar LED
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "dispositivo/led", "mensagem": "desligar"}'
```

---

## 📊 Códigos de Status HTTP

| Código | Descrição |
|--------|-----------|
| 200 | ✅ Sucesso |
| 201 | ✅ Criado com sucesso |
| 400 | ❌ Erro de validação |
| 404 | ❌ Tópico não encontrado |
| 500 | ❌ Erro interno do servidor |

---

## 🔧 Importar no Postman

1. **Baixe o arquivo**: `postman_collection.json`
2. **Abra o Postman**
3. **Clique em "Import"**
4. **Selecione o arquivo** `postman_collection.json`
5. **A coleção será importada** com todos os endpoints configurados

---

## 🧪 Testes Automáticos

Para testar todos os endpoints automaticamente:

```bash
./teste_api_ip.sh
```

---

## 📞 Suporte

- **IP**: 10.102.0.21
- **Porta**: 8000
- **Documentação**: `DOCUMENTACAO.md`
- **Configuração**: `CONFIGURACAO_SERVIDOR.md` 