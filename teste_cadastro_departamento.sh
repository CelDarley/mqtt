#!/bin/bash

echo "🧪 Teste de Cadastro de Departamento"
echo "=================================="

# 1. Primeiro, acessar a página de criação para obter o token e cookie de sessão
echo "📄 1. Obtendo formulário de criação..."
RESPONSE=$(curl -s -c cookies.txt "http://localhost:8002/departments/create")

# Verificar se foi redirecionado para login
if echo "$RESPONSE" | grep -q "Redirecting to.*login"; then
    echo "❌ Redirecionado para login - problema de autenticação"
    exit 1
fi

# 2. Extrair token CSRF
TOKEN=$(echo "$RESPONSE" | grep 'csrf-token' | sed 's/.*content=\"\([^\"]*\)\".*/\1/')
if [ -z "$TOKEN" ]; then
    echo "❌ Token CSRF não encontrado"
    exit 1
fi
echo "🔑 Token CSRF obtido: ${TOKEN:0:20}..."

# 3. Verificar se as empresas estão aparecendo
COMPANY_COUNT=$(echo "$RESPONSE" | grep -o '<option value="[0-9]*"' | wc -l)
echo "🏢 Empresas encontradas: $COMPANY_COUNT"

if [ $COMPANY_COUNT -eq 0 ]; then
    echo "❌ Nenhuma empresa encontrada no formulário"
    exit 1
fi

# 4. Obter ID da empresa Roboflex
ROBOFLEX_ID=$(echo "$RESPONSE" | grep -A1 'Roboflex' | grep 'option value' | sed 's/.*value=\"\([^\"]*\)\".*/\1/')
if [ -z "$ROBOFLEX_ID" ]; then
    echo "⚠️  Roboflex não encontrada, usando empresa ID 1"
    ROBOFLEX_ID=1
else
    echo "🏢 Roboflex encontrada com ID: $ROBOFLEX_ID"
fi

# 5. Fazer POST com os mesmos dados que você usou
echo "📝 2. Enviando dados do departamento..."
POST_RESPONSE=$(curl -s -b cookies.txt -i -X POST "http://localhost:8002/departments" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" \
    -H "Referer: http://localhost:8002/departments/create" \
    -d "_token=${TOKEN}&name=Diretoria técnica&id_comp=${ROBOFLEX_ID}&nivel_hierarquico=1&id_unid_up=")

# 6. Verificar resposta
HTTP_STATUS=$(echo "$POST_RESPONSE" | head -1)
echo "📊 Status HTTP: $HTTP_STATUS"

if echo "$POST_RESPONSE" | grep -q "419"; then
    echo "❌ Erro 419 - Page Expired (CSRF)"
    echo "🔍 Cabeçalhos da resposta:"
    echo "$POST_RESPONSE" | head -20
elif echo "$POST_RESPONSE" | grep -q "sucesso"; then
    echo "✅ Departamento criado com sucesso!"
elif echo "$POST_RESPONSE" | grep -q "erro"; then
    echo "❌ Erro ao criar departamento"
    echo "$POST_RESPONSE" | grep -i erro | head -3
else
    # Verificar se foi redirecionado
    if echo "$POST_RESPONSE" | grep -q "Location:"; then
        LOCATION=$(echo "$POST_RESPONSE" | grep "Location:" | cut -d' ' -f2)
        echo "🔄 Redirecionado para: $LOCATION"
        
        # Se redirecionado para index, verificar se há mensagem de sucesso
        if echo "$LOCATION" | grep -q "departments"; then
            echo "✅ Provavelmente criado com sucesso (redirecionado para lista)"
        fi
    else
        echo "⚠️  Resposta inesperada:"
        echo "$POST_RESPONSE" | tail -10
    fi
fi

# 7. Verificar logs do Laravel
echo ""
echo "📋 3. Verificando logs recentes..."
tail -n 5 storage/logs/laravel.log 2>/dev/null || echo "❌ Não foi possível acessar logs"

# Limpeza
rm -f cookies.txt

echo ""
echo "�� Teste concluído" 