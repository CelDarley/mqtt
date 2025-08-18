# 📋 Endpoints de Gerenciamento de Usuários

## 🎯 Visão Geral

Este documento descreve todos os endpoints disponíveis para gerenciamento de usuários na API MQTT. Os usuários podem ser do tipo **admin** ou **comum** e podem estar associados a uma companhia específica.

## 🔐 Campos do Usuário

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `name` | string | ✅ | Nome completo do usuário |
| `email` | string | ✅ | Email único do usuário |
| `password` | string | ✅ | Senha (mínimo 6 caracteres) |
| `phone` | string | ❌ | Telefone de contato |
| `id_comp` | integer | ❌ | ID da companhia associada |
| `tipo` | enum | ✅ | Tipo do usuário: `admin` ou `comum` |

## 🚀 Endpoints Disponíveis

### 1. 📝 **Criar Usuário**
- **URL**: `POST /api/users`
- **Descrição**: Cria um novo usuário no sistema
- **Body**:
```json
{
    "name": "João Silva",
    "email": "joao.silva@empresa.com",
    "password": "senha123",
    "phone": "(11) 99999-9999",
    "id_comp": 1,
    "tipo": "admin"
}
```
- **Resposta de Sucesso** (201):
```json
{
    "success": true,
    "message": "Usuário criado com sucesso",
    "data": {
        "id": 1,
        "name": "João Silva",
        "email": "joao.silva@empresa.com",
        "phone": "(11) 99999-9999",
        "id_comp": 1,
        "tipo": "admin",
        "created_at": "2024-08-08T10:00:00.000000Z",
        "updated_at": "2024-08-08T10:00:00.000000Z",
        "company": {
            "id": 1,
            "name": "Empresa XYZ"
        }
    }
}
```

### 2. 📋 **Listar Usuários**
- **URL**: `GET /api/users`
- **Descrição**: Lista todos os usuários com filtros opcionais
- **Parâmetros de Query**:
  - `search`: Busca por nome, email ou telefone
  - `tipo`: Filtra por tipo (admin/comum)
  - `id_comp`: Filtra por companhia
  - `order_by`: Campo para ordenação (padrão: name)
  - `order_dir`: Direção da ordenação (asc/desc)
  - `per_page`: Itens por página (padrão: 15)

**Exemplos de uso**:
```
GET /api/users?search=joão&tipo=admin&per_page=10
GET /api/users?id_comp=1&order_by=created_at&order_dir=desc
```

### 3. 🔍 **Buscar Usuário Específico**
- **URL**: `GET /api/users/{id}`
- **Descrição**: Retorna informações detalhadas de um usuário específico
- **Resposta**:
```json
{
    "success": true,
    "message": "Usuário encontrado com sucesso",
    "data": {
        "id": 1,
        "name": "João Silva",
        "email": "joao.silva@empresa.com",
        "phone": "(11) 99999-9999",
        "id_comp": 1,
        "tipo": "admin",
        "created_at": "2024-08-08T10:00:00.000000Z",
        "updated_at": "2024-08-08T10:00:00.000000Z",
        "company": {
            "id": 1,
            "name": "Empresa XYZ"
        }
    }
}
```

### 4. ✏️ **Atualizar Usuário**
- **URL**: `PUT /api/users/{id}`
- **Descrição**: Atualiza informações de um usuário existente
- **Body** (campos opcionais):
```json
{
    "name": "João Silva Santos",
    "phone": "(11) 88888-8888",
    "tipo": "comum"
}
```

### 5. 🗑️ **Excluir Usuário**
- **URL**: `DELETE /api/users/{id}`
- **Descrição**: Remove um usuário do sistema
- **Validações**:
  - Não permite excluir o último administrador
  - Remove todas as associações do usuário

### 6. 🔐 **Trocar Senha**
- **URL**: `PATCH /api/users/{id}/change-password`
- **Descrição**: Permite ao usuário alterar sua própria senha
- **Body**:
```json
{
    "current_password": "senha123",
    "new_password": "novaSenha456",
    "confirm_password": "novaSenha456"
}
```

### 7. 🔎 **Pesquisa Avançada**
- **URL**: `GET /api/users/search`
- **Descrição**: Pesquisa avançada com múltiplos filtros
- **Parâmetros**:
  - `q`: Busca geral por nome, email ou telefone
  - `name`: Busca específica por nome
  - `email`: Busca específica por email
  - `phone`: Busca específica por telefone
  - `tipo`: Filtra por tipo
  - `id_comp`: Filtra por companhia
  - `created_from`: Data de criação a partir de
  - `created_to`: Data de criação até
  - `order_by`: Campo para ordenação
  - `order_dir`: Direção da ordenação
  - `per_page`: Itens por página

**Exemplo**:
```
GET /api/users/search?q=joão&tipo=admin&created_from=2024-01-01&per_page=20
```

### 8. 🏢 **Usuários por Companhia**
- **URL**: `GET /api/users/company/{companyId}`
- **Descrição**: Lista todos os usuários de uma companhia específica
- **Resposta**:
```json
{
    "success": true,
    "message": "Usuários da companhia listados com sucesso",
    "data": {
        "company": {
            "id": 1,
            "name": "Empresa XYZ"
        },
        "users": [...],
        "total_users": 5,
        "admin_count": 2,
        "common_count": 3
    }
}
```

### 9. 📊 **Estatísticas dos Usuários**
- **URL**: `GET /api/users/stats`
- **Descrição**: Retorna estatísticas gerais sobre usuários
- **Resposta**:
```json
{
    "success": true,
    "message": "Estatísticas obtidas com sucesso",
    "data": {
        "total_users": 25,
        "admin_users": 3,
        "common_users": 22,
        "users_with_company": 20,
        "users_without_company": 5,
        "users_with_phone": 18,
        "users_without_phone": 7,
        "companies_with_users": 8
    }
}
```

## 🔒 Validações e Regras de Negócio

### Validações de Entrada
- **Nome**: Obrigatório, máximo 255 caracteres
- **Email**: Obrigatório, formato válido, único no sistema
- **Senha**: Obrigatória, mínimo 6 caracteres
- **Telefone**: Opcional, máximo 20 caracteres
- **Companhia**: Opcional, deve existir na tabela companies
- **Tipo**: Obrigatório, apenas 'admin' ou 'comum'

### Regras de Negócio
1. **Exclusão de Administradores**: Não é possível excluir o último administrador do sistema
2. **Unicidade de Email**: Cada email pode ser usado por apenas um usuário
3. **Associação com Companhia**: Usuários podem existir sem estar associados a uma companhia
4. **Troca de Senha**: Requer confirmação da senha atual e nova senha

## 📝 Exemplos de Uso Completos

### Criar Usuário Administrador
```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Administrador Sistema",
    "email": "admin@sistema.com",
    "password": "admin123",
    "phone": "(11) 99999-9999",
    "tipo": "admin"
  }'
```

### Criar Usuário Comum com Companhia
```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Funcionário XYZ",
    "email": "funcionario@empresa.com",
    "password": "senha123",
    "phone": "(11) 88888-8888",
    "id_comp": 1,
    "tipo": "comum"
  }'
```

### Buscar Usuários Administradores
```bash
curl "http://localhost:8000/api/users?tipo=admin&per_page=10"
```

### Pesquisar Usuários por Nome
```bash
curl "http://localhost:8000/api/users/search?name=joão&tipo=admin"
```

### Trocar Senha
```bash
curl -X PATCH http://localhost:8000/api/users/1/change-password \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "senha123",
    "new_password": "novaSenha456",
    "confirm_password": "novaSenha456"
  }'
```

## 🚨 Códigos de Erro

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso na operação |
| 201 | Usuário criado com sucesso |
| 400 | Erro de validação ou regra de negócio |
| 404 | Usuário não encontrado |
| 422 | Erro de validação dos dados |
| 500 | Erro interno do servidor |

## 🔄 Relacionamentos

### Usuário → Companhia
- Relacionamento `belongsTo` com a tabela `companies`
- Campo de ligação: `id_comp`
- Comportamento: `onDelete('set null')` - se a companhia for excluída, o usuário fica sem companhia

### Companhia → Usuários
- Relacionamento `hasMany` com a tabela `users`
- Acessível através do método `users()` no modelo Company

## 📱 Compatibilidade

- **API REST**: Todos os endpoints seguem padrões REST
- **JSON**: Todas as requisições e respostas são em formato JSON
- **Paginação**: Suporte a paginação com parâmetros personalizáveis
- **Filtros**: Sistema robusto de filtros e busca
- **Ordenação**: Ordenação por qualquer campo da tabela
- **Validação**: Validação completa de entrada com mensagens em português

