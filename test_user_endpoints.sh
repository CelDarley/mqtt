#!/bin/bash

# Script de teste para endpoints de usuário
# Base URL da API
BASE_URL="http://localhost:8000/api"

echo "🧪 Testando Endpoints de Usuário"
echo "================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para testar endpoint
test_endpoint() {
    local method=$1
    local endpoint=$2
    local data=$3
    local description=$4

    echo -e "\n${YELLOW}🔍 Testando: $description${NC}"
    echo "URL: $method $BASE_URL$endpoint"

    if [ "$method" = "GET" ]; then
        response=$(curl -s -w "\n%{http_code}" "$BASE_URL$endpoint")
    else
        response=$(curl -s -w "\n%{http_code}" -X "$method" \
            -H "Content-Type: application/json" \
            -d "$data" \
            "$BASE_URL$endpoint")
    fi

    # Separar resposta e código HTTP
    http_code=$(echo "$response" | tail -n1)
    response_body=$(echo "$response" | head -n -1)

    if [ "$http_code" -ge 200 ] && [ "$http_code" -lt 300 ]; then
        echo -e "${GREEN}✅ Sucesso (HTTP $http_code)${NC}"
        echo "Resposta: $response_body"
    else
        echo -e "${RED}❌ Erro (HTTP $http_code)${NC}"
        echo "Resposta: $response_body"
    fi

    echo "---"
}

# 1. Criar usuário administrador
echo -e "\n${GREEN}1. Criando usuário administrador...${NC}"
test_endpoint "POST" "/users" '{
    "name": "Administrador Sistema",
    "email": "admin@sistema.com",
    "password": "admin123",
    "phone": "(11) 99999-9999",
    "tipo": "admin"
}' "Criar usuário administrador"

# 2. Criar usuário comum
echo -e "\n${GREEN}2. Criando usuário comum...${NC}"
test_endpoint "POST" "/users" '{
    "name": "Funcionário Teste",
    "email": "funcionario@teste.com",
    "password": "senha123",
    "phone": "(11) 88888-8888",
    "tipo": "comum"
}' "Criar usuário comum"

# 3. Listar todos os usuários
echo -e "\n${GREEN}3. Listando todos os usuários...${NC}"
test_endpoint "GET" "/users" "" "Listar todos os usuários"

# 4. Buscar usuários por tipo
echo -e "\n${GREEN}4. Buscando usuários administradores...${NC}"
test_endpoint "GET" "/users?tipo=admin" "" "Buscar usuários admin"

# 5. Buscar usuários por tipo
echo -e "\n${GREEN}5. Buscando usuários comuns...${NC}"
test_endpoint "GET" "/users?tipo=comum" "" "Buscar usuários comuns"

# 6. Pesquisa avançada
echo -e "\n${GREEN}6. Pesquisa avançada por nome...${NC}"
test_endpoint "GET" "/users/search?name=admin" "" "Pesquisa avançada"

# 7. Estatísticas dos usuários
echo -e "\n${GREEN}7. Obtendo estatísticas...${NC}"
test_endpoint "GET" "/users/stats" "" "Estatísticas dos usuários"

# 8. Testar busca por usuário específico (ID 1)
echo -e "\n${GREEN}8. Buscando usuário específico (ID 1)...${NC}"
test_endpoint "GET" "/users/1" "" "Buscar usuário por ID"

echo -e "\n${GREEN}🎉 Testes concluídos!${NC}"
echo "Verifique as respostas acima para confirmar o funcionamento dos endpoints."

