# 🏢 Endpoints de Companhia e Departamento

Este documento descreve todos os endpoints disponíveis para gerenciar companhias e departamentos com estrutura organizacional hierárquica.

## 📋 Visão Geral

A API permite criar e gerenciar:
- **Companhias**: Entidades principais que contêm departamentos
- **Departamentos**: Unidades organizacionais com estrutura hierárquica

### Estrutura Hierárquica
- **Nível 1**: Departamentos raiz (sem unidade superior)
- **Nível 2+**: Departamentos subordinados (com unidade superior)
- **Auto-relacionamento**: Cada departamento pode ter uma unidade superior (`id_unid_up`)

## 🏢 Endpoints de Companhia

### Base URL
```
{{base_url}}/api/companies
```

### 1. **Listar Todas as Companhias**
- **Método**: `GET`
- **URL**: `/api/companies`
- **Descrição**: Retorna todas as companhias com contagem de departamentos

#### Response:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "TechCorp Ltda",
            "departments_count": 5,
            "created_at": "2024-08-08T18:00:00.000000Z",
            "updated_at": "2024-08-08T18:00:00.000000Z"
        }
    ]
}
```

### 2. **Criar Nova Companhia**
- **Método**: `POST`
- **URL**: `/api/companies`
- **Descrição**: Cria uma nova companhia

#### Request Body:
```json
{
    "name": "TechCorp Ltda"
}
```

#### Response:
```json
{
    "success": true,
    "message": "Companhia criada com sucesso",
    "data": {
        "id": 1,
        "name": "TechCorp Ltda",
        "created_at": "2024-08-08T18:00:00.000000Z",
        "updated_at": "2024-08-08T18:00:00.000000Z"
    }
}
```

### 3. **Obter Companhia Específica**
- **Método**: `GET`
- **URL**: `/api/companies/{id}`
- **Descrição**: Retorna uma companhia específica com seus departamentos

#### Response:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "TechCorp Ltda",
        "created_at": "2024-08-08T18:00:00.000000Z",
        "updated_at": "2024-08-08T18:00:00.000000Z",
        "departments": [
            {
                "id": 1,
                "name": "Diretoria",
                "nivel_hierarquico": 1,
                "id_unid_up": null,
                "id_comp": 1
            }
        ]
    }
}
```

### 4. **Atualizar Companhia**
- **Método**: `PUT`
- **URL**: `/api/companies/{id}`
- **Descrição**: Atualiza o nome de uma companhia

#### Request Body:
```json
{
    "name": "TechCorp Internacional Ltda"
}
```

#### Response:
```json
{
    "success": true,
    "message": "Companhia atualizada com sucesso",
    "data": {
        "id": 1,
        "name": "TechCorp Internacional Ltda",
        "updated_at": "2024-08-08T18:05:00.000000Z"
    }
}
```

### 5. **Deletar Companhia**
- **Método**: `DELETE`
- **URL**: `/api/companies/{id}`
- **Descrição**: Remove uma companhia (apenas se não tiver departamentos)

#### Response:
```json
{
    "success": true,
    "message": "Companhia deletada com sucesso"
}
```

### 6. **Estrutura Organizacional**
- **Método**: `GET`
- **URL**: `/api/companies/{id}/structure`
- **Descrição**: Retorna a estrutura organizacional agrupada por nível

#### Response:
```json
{
    "success": true,
    "data": {
        "company": {
            "id": 1,
            "name": "TechCorp Ltda"
        },
        "structure": {
            "1": [
                {
                    "id": 1,
                    "name": "Diretoria",
                    "nivel_hierarquico": 1
                }
            ],
            "2": [
                {
                    "id": 2,
                    "name": "TI",
                    "nivel_hierarquico": 2,
                    "id_unid_up": 1
                }
            ]
        },
        "statistics": {
            "total_departments": 5,
            "root_departments": 1,
            "leaf_departments": 3,
            "middle_departments": 1,
            "max_hierarchy_level": 3
        }
    }
}
```

### 7. **Árvore Organizacional**
- **Método**: `GET`
- **URL**: `/api/companies/{id}/tree`
- **Descrição**: Retorna a árvore organizacional hierárquica

#### Response:
```json
{
    "success": true,
    "data": {
        "company": {
            "id": 1,
            "name": "TechCorp Ltda"
        },
        "tree": [
            {
                "id": 1,
                "name": "Diretoria",
                "nivel_hierarquico": 1,
                "children": [
                    {
                        "id": 2,
                        "name": "TI",
                        "nivel_hierarquico": 2,
                        "children": [
                            {
                                "id": 3,
                                "name": "Desenvolvimento",
                                "nivel_hierarquico": 3
                            }
                        ]
                    }
                ]
            }
        ]
    }
}
```

## 🏢 Endpoints de Departamento

### Base URL
```
{{base_url}}/api/departments
```

### 1. **Listar Departamentos**
- **Método**: `GET`
- **URL**: `/api/departments`
- **Descrição**: Lista departamentos com filtros opcionais

#### Query Parameters:
- `company_id`: Filtrar por companhia
- `nivel_hierarquico`: Filtrar por nível hierárquico
- `id_unid_up`: Filtrar por unidade superior

#### Exemplo:
```
GET /api/departments?company_id=1&nivel_hierarquico=2
```

#### Response:
```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "name": "TI",
            "nivel_hierarquico": 2,
            "id_unid_up": 1,
            "id_comp": 1,
            "company": {
                "id": 1,
                "name": "TechCorp Ltda"
            },
            "parent": {
                "id": 1,
                "name": "Diretoria"
            }
        }
    ]
}
```

### 2. **Criar Departamento**
- **Método**: `POST`
- **URL**: `/api/departments`
- **Descrição**: Cria um novo departamento

#### Request Body:
```json
{
    "name": "Recursos Humanos",
    "nivel_hierarquico": 2,
    "id_unid_up": 1,
    "id_comp": 1
}
```

#### Validações:
- `nivel_hierarquico` deve ser >= 1
- Se `nivel_hierarquico` > 1, `id_unid_up` é obrigatório
- `id_unid_up` deve pertencer à mesma companhia
- `nivel_hierarquico` deve ser `nivel_unid_up + 1`

#### Response:
```json
{
    "success": true,
    "message": "Departamento criado com sucesso",
    "data": {
        "id": 3,
        "name": "Recursos Humanos",
        "nivel_hierarquico": 2,
        "id_unid_up": 1,
        "id_comp": 1,
        "company": {
            "id": 1,
            "name": "TechCorp Ltda"
        },
        "parent": {
            "id": 1,
            "name": "Diretoria"
        }
    }
}
```

### 3. **Obter Departamento**
- **Método**: `GET`
- **URL**: `/api/departments/{id}`
- **Descrição**: Retorna um departamento específico com relacionamentos

#### Response:
```json
{
    "success": true,
    "data": {
        "id": 2,
        "name": "TI",
        "nivel_hierarquico": 2,
        "id_unid_up": 1,
        "id_comp": 1,
        "company": {
            "id": 1,
            "name": "TechCorp Ltda"
        },
        "parent": {
            "id": 1,
            "name": "Diretoria"
        },
        "children": [
            {
                "id": 3,
                "name": "Desenvolvimento",
                "nivel_hierarquico": 3
            }
        ]
    }
}
```

### 4. **Atualizar Departamento**
- **Método**: `PUT`
- **URL**: `/api/departments/{id}`
- **Descrição**: Atualiza um departamento existente

#### Request Body:
```json
{
    "name": "Tecnologia da Informação",
    "nivel_hierarquico": 2,
    "id_unid_up": 1,
    "id_comp": 1
}
```

#### Response:
```json
{
    "success": true,
    "message": "Departamento atualizado com sucesso",
    "data": {
        "id": 2,
        "name": "Tecnologia da Informação",
        "nivel_hierarquico": 2,
        "id_unid_up": 1,
        "id_comp": 1
    }
}
```

### 5. **Deletar Departamento**
- **Método**: `DELETE`
- **URL**: `/api/departments/{id}`
- **Descrição**: Remove um departamento (apenas se não tiver subordinados)

#### Response:
```json
{
    "success": true,
    "message": "Departamento deletado com sucesso"
}
```

### 6. **Hierarquia do Departamento**
- **Método**: `GET`
- **URL**: `/api/departments/{id}/hierarchy`
- **Descrição**: Retorna informações hierárquicas do departamento

#### Response:
```json
{
    "success": true,
    "data": {
        "department": {
            "id": 3,
            "name": "Desenvolvimento"
        },
        "hierarchy_path": "Diretoria > TI > Desenvolvimento",
        "siblings": [
            {
                "id": 4,
                "name": "Infraestrutura"
            }
        ],
        "is_root": false,
        "is_leaf": true
    }
}
```

### 7. **Departamentos por Companhia**
- **Método**: `GET`
- **URL**: `/api/departments/company/{companyId}`
- **Descrição**: Retorna todos os departamentos de uma companhia

#### Response:
```json
{
    "success": true,
    "data": {
        "company": {
            "id": 1,
            "name": "TechCorp Ltda"
        },
        "departments": [...],
        "structure": {...},
        "statistics": {...}
    }
}
```

### 8. **Mover Departamento**
- **Método**: `PATCH`
- **URL**: `/api/departments/{id}/move`
- **Descrição**: Move um departamento na hierarquia

#### Request Body:
```json
{
    "new_parent_id": 2,
    "new_level": 3
}
```

#### Response:
```json
{
    "success": true,
    "message": "Departamento movido com sucesso",
    "data": {
        "id": 3,
        "name": "Desenvolvimento",
        "nivel_hierarquico": 3,
        "id_unid_up": 2
    }
}
```

## 🔧 Validações e Regras de Negócio

### Companhia
- Nome deve ser único
- Não pode ser deletada se tiver departamentos

### Departamento
- Nome é obrigatório
- Nível hierárquico deve ser >= 1
- Se nível > 1, deve ter unidade superior
- Unidade superior deve pertencer à mesma companhia
- Nível deve ser `nivel_unid_up + 1`
- Não pode ser deletado se tiver subordinados
- Não pode referenciar a si mesmo como unidade superior

## 📊 Exemplos de Uso

### Criar Estrutura Organizacional Completa

1. **Criar Companhia**:
```bash
POST /api/companies
{
    "name": "TechCorp Ltda"
}
```

2. **Criar Diretoria (Nível 1)**:
```bash
POST /api/departments
{
    "name": "Diretoria",
    "nivel_hierarquico": 1,
    "id_comp": 1
}
```

3. **Criar TI (Nível 2)**:
```bash
POST /api/departments
{
    "name": "TI",
    "nivel_hierarquico": 2,
    "id_unid_up": 1,
    "id_comp": 1
}
```

4. **Criar Desenvolvimento (Nível 3)**:
```bash
POST /api/departments
{
    "name": "Desenvolvimento",
    "nivel_hierarquico": 3,
    "id_unid_up": 2,
    "id_comp": 1
}
```

### Visualizar Estrutura

```bash
GET /api/companies/1/structure
GET /api/companies/1/tree
GET /api/departments/company/1
```

## 🚨 Códigos de Erro

- **400**: Erro de validação
- **404**: Recurso não encontrado
- **422**: Erro de validação de negócio
- **500**: Erro interno do servidor

## 🔗 Relacionamentos

- **Company** → **Department** (1:N)
- **Department** → **Department** (1:N) - Auto-relacionamento hierárquico
- **Department** → **Company** (N:1)

---

**🎉 Agora você tem uma API completa para gerenciar companhias e departamentos com estrutura organizacional hierárquica!**
