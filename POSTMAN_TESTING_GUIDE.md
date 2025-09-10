# 🚀 Guia de Testes da API MQTT no Postman

Este guia fornece instruções completas para testar todos os endpoints da API MQTT Laravel usando o Postman.

## 📋 Configuração Inicial

### 1. Configurar Environment no Postman

Crie um novo environment com as seguintes variáveis:

| Variable | Initial Value | Current Value |
|----------|---------------|---------------|
| `base_url` | `http://10.100.0.200:8000` | `http://10.100.0.200:8000` |
| `mqtt_server` | `10.100.0.21` | `10.100.0.21` |
| `mqtt_port` | `1883` | `1883` |
| `mqtt_username` | `darley` | `darley` |
| `mqtt_password` | `yhvh77` | `yhvh77` |

### 2. Configurar Headers Globais

Adicione estes headers em todas as requisições:

```
Content-Type: application/json
Accept: application/json
```

## 🔧 Endpoints Disponíveis

### 1. **Listar Tópicos MQTT**
- **Método**: `GET`
- **URL**: `{{base_url}}/api/mqtt/topics`
- **Descrição**: Retorna todos os tópicos MQTT cadastrados

#### Request:
```
GET {{base_url}}/api/mqtt/topics
```

#### Response Esperada:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "sensor/temperatura",
            "description": "Dados de temperatura do sensor",
            "created_at": "2024-08-08T17:30:00.000000Z",
            "updated_at": "2024-08-08T17:30:00.000000Z"
        }
    ]
}
```

### 2. **Criar Novo Tópico**
- **Método**: `POST`
- **URL**: `{{base_url}}/api/mqtt/topics`
- **Descrição**: Cria um novo tópico MQTT

#### Request:
```
POST {{base_url}}/api/mqtt/topics
```

#### Body (raw JSON):
```json
{
    "name": "sensor/umidade",
    "description": "Dados de umidade do sensor"
}
```

#### Response Esperada:
```json
{
    "success": true,
    "message": "Tópico criado com sucesso",
    "data": {
        "id": 2,
        "name": "sensor/umidade",
        "description": "Dados de umidade do sensor",
        "created_at": "2024-08-08T17:35:00.000000Z",
        "updated_at": "2024-08-08T17:35:00.000000Z"
    }
}
```

### 3. **Enviar Mensagem MQTT**
- **Método**: `POST`
- **URL**: `{{base_url}}/api/mqtt/send-message`
- **Descrição**: Envia uma mensagem para um tópico MQTT específico

#### Request:
```
POST {{base_url}}/api/mqtt/send-message
```

#### Body (raw JSON):
```json
{
    "topico": "sensor/temperatura",
    "mensagem": "25.5"
}
```

#### Response Esperada:
```json
{
    "success": true,
    "message": "Mensagem enviada com sucesso para o tópico sensor/temperatura",
    "data": {
        "topic": "sensor/temperatura",
        "message": "25.5",
        "timestamp": "2024-08-08T17:40:00.000000Z"
    }
}
```

### 4. **Obter Tópico por ID**
- **Método**: `GET`
- **URL**: `{{base_url}}/api/mqtt/topics/{id}`
- **Descrição**: Retorna um tópico específico por ID

#### Request:
```
GET {{base_url}}/api/mqtt/topics/1
```

#### Response Esperada:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "sensor/temperatura",
        "description": "Dados de temperatura do sensor",
        "created_at": "2024-08-08T17:30:00.000000Z",
        "updated_at": "2024-08-08T17:30:00.000000Z"
    }
}
```

### 5. **Atualizar Tópico**
- **Método**: `PUT`
- **URL**: `{{base_url}}/api/mqtt/topics/{id}`
- **Descrição**: Atualiza um tópico existente

#### Request:
```
PUT {{base_url}}/api/mqtt/topics/1
```

#### Body (raw JSON):
```json
{
    "name": "sensor/temperatura/atualizada",
    "description": "Dados de temperatura atualizados"
}
```

#### Response Esperada:
```json
{
    "success": true,
    "message": "Tópico atualizado com sucesso",
    "data": {
        "id": 1,
        "name": "sensor/temperatura/atualizada",
        "description": "Dados de temperatura atualizados",
        "updated_at": "2024-08-08T17:45:00.000000Z"
    }
}
```

### 6. **Deletar Tópico**
- **Método**: `DELETE`
- **URL**: `{{base_url}}/api/mqtt/topics/{id}`
- **Descrição**: Remove um tópico específico

#### Request:
```
DELETE {{base_url}}/api/mqtt/topics/1
```

#### Response Esperada:
```json
{
    "success": true,
    "message": "Tópico deletado com sucesso"
}
```

## 🧪 Coleção de Testes Postman

### Importar Coleção

1. **Baixar arquivo**: `postman_collection.json` do repositório
2. **No Postman**: File → Import → Upload Files
3. **Selecionar**: `postman_collection.json`
4. **Importar**: A coleção será adicionada ao seu workspace

### Estrutura da Coleção

```
📁 API MQTT Laravel
├── 🔍 GET - Listar Tópicos
├── ➕ POST - Criar Tópico
├── 📤 POST - Enviar Mensagem
├── 🔍 GET - Obter Tópico por ID
├── ✏️ PUT - Atualizar Tópico
└── 🗑️ DELETE - Deletar Tópico
```

## 🎯 Cenários de Teste

### Cenário 1: Fluxo Básico de Tópicos

1. **Listar tópicos** (deve retornar array vazio inicialmente)
2. **Criar primeiro tópico** (sensor/temperatura)
3. **Criar segundo tópico** (sensor/umidade)
4. **Listar tópicos** (deve retornar 2 tópicos)
5. **Obter tópico por ID** (testar com ID 1)
6. **Atualizar tópico** (modificar descrição)
7. **Deletar tópico** (remover um dos tópicos)

### Cenário 2: Teste de Mensagens MQTT

1. **Criar tópico** (teste/mensagens)
2. **Enviar mensagem simples** ("Hello World")
3. **Enviar mensagem JSON** ({"valor": 42, "unidade": "celsius"})
4. **Enviar mensagem para tópico inexistente** (deve retornar erro)

### Cenário 3: Validações e Erros

1. **Criar tópico sem nome** (deve retornar erro de validação)
2. **Criar tópico com nome duplicado** (deve retornar erro)
3. **Acessar tópico inexistente** (deve retornar 404)
4. **Enviar mensagem sem tópico** (deve retornar erro)

## 🔍 Testes Automatizados

### Adicionar Tests nos Requests

#### Para Listar Tópicos:
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success property", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success');
    pm.expect(jsonData.success).to.eql(true);
});

pm.test("Response has data array", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
    pm.expect(jsonData.data).to.be.an('array');
});
```

#### Para Criar Tópico:
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Tópico criado com sucesso", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.eql(true);
    pm.expect(jsonData.message).to.include("criado com sucesso");
});

pm.test("Tópico tem ID", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('id');
    pm.expect(jsonData.data.id).to.be.a('number');
});

// Salvar ID para uso posterior
if (pm.response.json().success) {
    pm.environment.set("last_topic_id", pm.response.json().data.id);
}
```

#### Para Enviar Mensagem:
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Mensagem enviada com sucesso", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.eql(true);
    pm.expect(jsonData.message).to.include("enviada com sucesso");
});

pm.test("Resposta contém dados da mensagem", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('topic');
    pm.expect(jsonData.data).to.have.property('message');
    pm.expect(jsonData.data).to.have.property('timestamp');
});
```

## 🚨 Tratamento de Erros

### Erro 400 - Validação
```json
{
    "success": false,
    "message": "Erro de validação",
    "errors": {
        "name": ["O campo nome é obrigatório"],
        "description": ["O campo descrição é obrigatório"]
    }
}
```

### Erro 404 - Não Encontrado
```json
{
    "success": false,
    "message": "Tópico não encontrado"
}
```

### Erro 500 - Erro Interno
```json
{
    "success": false,
    "message": "Erro interno do servidor"
}
```

## 🔧 Configurações Avançadas

### 1. Pre-request Scripts

#### Para Criar Tópico (gerar nome único):
```javascript
// Gerar nome único para o tópico
var timestamp = new Date().getTime();
var topicName = "teste/" + timestamp;
pm.environment.set("unique_topic_name", topicName);
```

#### Para Enviar Mensagem (usar tópico criado):
```javascript
// Usar o último tópico criado
var lastTopicId = pm.environment.get("last_topic_id");
if (lastTopicId) {
    pm.environment.set("current_topic_id", lastTopicId);
}
```

### 2. Variables de Ambiente Dinâmicas

```javascript
// Salvar token de autenticação (se implementado)
if (pm.response.json().token) {
    pm.environment.set("auth_token", pm.response.json().token);
}

// Salvar URL base do servidor
pm.environment.set("server_ip", pm.response.json().server_info.ip);
```

## 📊 Monitoramento e Logs

### 1. Verificar Logs da API
```bash
# No servidor
journalctl -u api-mqtt -f
```

### 2. Verificar Logs MQTT
```bash
# No servidor MQTT
tail -f /var/log/mosquitto/mosquitto.log
```

### 3. Testar Conectividade MQTT
```bash
# Testar se o servidor MQTT está respondendo
mosquitto_pub -h 10.100.0.21 -u darley -P yhvh77 -t "teste/conexao" -m "teste"
```

## 🎯 Checklist de Testes

### ✅ Funcionalidades Básicas
- [ ] Listar tópicos (GET /api/mqtt/topics)
- [ ] Criar tópico (POST /api/mqtt/topics)
- [ ] Enviar mensagem (POST /api/mqtt/send-message)
- [ ] Obter tópico por ID (GET /api/mqtt/topics/{id})
- [ ] Atualizar tópico (PUT /api/mqtt/topics/{id})
- [ ] Deletar tópico (DELETE /api/mqtt/topics/{id})

### ✅ Validações
- [ ] Campos obrigatórios
- [ ] Formato de dados
- [ ] Unicidade de nomes
- [ ] Tratamento de erros

### ✅ Cenários de Erro
- [ ] Tópico inexistente
- [ ] Dados inválidos
- [ ] Servidor MQTT indisponível
- [ ] Erro interno do servidor

### ✅ Performance
- [ ] Tempo de resposta < 2 segundos
- [ ] Múltiplas requisições simultâneas
- [ ] Carga de dados (muitos tópicos)

## 🔗 Links Úteis

- **Documentação da API**: `README.md`
- **Endpoints Rápido**: `ENDPOINTS_RAPIDO.md`
- **Coleção Postman**: `postman_collection.json`
- **Repositório**: https://github.com/CelDarley/mqtt

## 📞 Suporte

Se encontrar problemas durante os testes:

1. **Verificar logs**: `journalctl -u api-mqtt -f`
2. **Verificar status**: `systemctl status api-mqtt`
3. **Testar conectividade**: `curl http://10.100.0.200:8000/api/mqtt/topics`
4. **Verificar MQTT**: `mosquitto_pub` para testar servidor MQTT

---

**🎉 Agora você está pronto para testar completamente a API MQTT Laravel no Postman!** 