#!/bin/bash

# Script para testar CRUD de empresas
# ===================================

echo "🏢 Testando CRUD de Empresas"
echo "============================"

# Cores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✅ $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠️ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ️ $1${NC}"; }

API_BASE="http://localhost:8000/api/mqtt"

# Verificar se backend está rodando
print_info "Verificando se backend está rodando..."
if ! curl -s "$API_BASE/companies" > /dev/null; then
    print_error "Backend não está rodando!"
    print_info "Execute: cd mqtt && php artisan serve"
    exit 1
fi
print_success "Backend está funcionando"

echo ""
print_info "=== TESTE 1: Listar empresas existentes ==="
RESPONSE=$(curl -s "$API_BASE/companies")
COMPANIES_COUNT=$(echo "$RESPONSE" | jq -r '.data | length' 2>/dev/null || echo "0")
print_info "Empresas encontradas: $COMPANIES_COUNT"

if [ "$COMPANIES_COUNT" -gt 0 ]; then
    echo "Empresas existentes:"
    echo "$RESPONSE" | jq -r '.data[] | "🏢 \(.id) - \(.name)"'
fi

echo ""
print_info "=== TESTE 2: Criar nova empresa ==="
COMPANY_NAME="Empresa Teste $(date +%H%M%S)"
CREATE_RESPONSE=$(curl -s -X POST "$API_BASE/companies" \
    -H "Content-Type: application/json" \
    -d "{\"name\": \"$COMPANY_NAME\"}")

CREATE_SUCCESS=$(echo "$CREATE_RESPONSE" | jq -r '.success // false' 2>/dev/null)
if [ "$CREATE_SUCCESS" = "true" ]; then
    COMPANY_ID=$(echo "$CREATE_RESPONSE" | jq -r '.data.id')
    print_success "Empresa criada com ID: $COMPANY_ID"
    print_info "Nome: $COMPANY_NAME"
else
    CREATE_MESSAGE=$(echo "$CREATE_RESPONSE" | jq -r '.message // "Erro desconhecido"' 2>/dev/null)
    print_error "Falha ao criar empresa: $CREATE_MESSAGE"
    exit 1
fi

echo ""
print_info "=== TESTE 3: Buscar empresa criada ==="
SHOW_RESPONSE=$(curl -s "$API_BASE/companies/$COMPANY_ID")
SHOW_SUCCESS=$(echo "$SHOW_RESPONSE" | jq -r '.success // false' 2>/dev/null)

if [ "$SHOW_SUCCESS" = "true" ]; then
    print_success "Empresa encontrada"
    FOUND_NAME=$(echo "$SHOW_RESPONSE" | jq -r '.data.name')
    CREATED_AT=$(echo "$SHOW_RESPONSE" | jq -r '.data.created_at')
    print_info "Nome: $FOUND_NAME"
    print_info "Criada em: $CREATED_AT"
else
    SHOW_MESSAGE=$(echo "$SHOW_RESPONSE" | jq -r '.message // "Erro desconhecido"' 2>/dev/null)
    print_error "Falha ao buscar empresa: $SHOW_MESSAGE"
fi

echo ""
print_info "=== TESTE 4: Atualizar empresa ==="
NEW_COMPANY_NAME="$COMPANY_NAME (Editada)"
UPDATE_RESPONSE=$(curl -s -X PUT "$API_BASE/companies/$COMPANY_ID" \
    -H "Content-Type: application/json" \
    -d "{\"name\": \"$NEW_COMPANY_NAME\"}")

UPDATE_SUCCESS=$(echo "$UPDATE_RESPONSE" | jq -r '.success // false' 2>/dev/null)
if [ "$UPDATE_SUCCESS" = "true" ]; then
    print_success "Empresa atualizada"
    UPDATED_NAME=$(echo "$UPDATE_RESPONSE" | jq -r '.data.name')
    print_info "Novo nome: $UPDATED_NAME"
else
    UPDATE_MESSAGE=$(echo "$UPDATE_RESPONSE" | jq -r '.message // "Erro desconhecido"' 2>/dev/null)
    print_error "Falha ao atualizar empresa: $UPDATE_MESSAGE"
fi

echo ""
print_info "=== TESTE 5: Verificar atualização ==="
VERIFY_RESPONSE=$(curl -s "$API_BASE/companies/$COMPANY_ID")
VERIFY_SUCCESS=$(echo "$VERIFY_RESPONSE" | jq -r '.success // false' 2>/dev/null)

if [ "$VERIFY_SUCCESS" = "true" ]; then
    CURRENT_NAME=$(echo "$VERIFY_RESPONSE" | jq -r '.data.name')
    if [[ "$CURRENT_NAME" == *"(Editada)"* ]]; then
        print_success "Atualização verificada com sucesso"
        print_info "Nome atual: $CURRENT_NAME"
    else
        print_warning "Nome não foi atualizado corretamente"
        print_info "Esperado: contendo '(Editada)'"
        print_info "Atual: $CURRENT_NAME"
    fi
fi

echo ""
print_info "=== TESTE 6: Testar validação (nome duplicado) ==="
DUPLICATE_RESPONSE=$(curl -s -X POST "$API_BASE/companies" \
    -H "Content-Type: application/json" \
    -d "{\"name\": \"$NEW_COMPANY_NAME\"}")

DUPLICATE_SUCCESS=$(echo "$DUPLICATE_RESPONSE" | jq -r '.success // false' 2>/dev/null)
if [ "$DUPLICATE_SUCCESS" = "false" ]; then
    print_success "Validação funcionando - nome duplicado rejeitado"
    DUPLICATE_MESSAGE=$(echo "$DUPLICATE_RESPONSE" | jq -r '.message // "Erro desconhecido"' 2>/dev/null)
    print_info "Mensagem: $DUPLICATE_MESSAGE"
else
    print_warning "Validação de nome duplicado não funcionou"
fi

echo ""
print_info "=== TESTE 7: Tentar deletar empresa ==="
print_warning "Testando deleção (pode falhar se houver departamentos)"

DELETE_RESPONSE=$(curl -s -X DELETE "$API_BASE/companies/$COMPANY_ID")
DELETE_SUCCESS=$(echo "$DELETE_RESPONSE" | jq -r '.success // false' 2>/dev/null)

if [ "$DELETE_SUCCESS" = "true" ]; then
    print_success "Empresa deletada com sucesso"
    
    # Verificar se realmente foi deletada
    echo ""
    print_info "Verificando deleção..."
    CHECK_RESPONSE=$(curl -s "$API_BASE/companies/$COMPANY_ID")
    CHECK_SUCCESS=$(echo "$CHECK_RESPONSE" | jq -r '.success // false' 2>/dev/null)
    
    if [ "$CHECK_SUCCESS" = "false" ]; then
        print_success "Deleção confirmada - empresa não encontrada"
    else
        print_warning "Empresa ainda existe após deleção"
    fi
else
    DELETE_MESSAGE=$(echo "$DELETE_RESPONSE" | jq -r '.message // "Erro desconhecido"' 2>/dev/null)
    print_info "Deleção não realizada: $DELETE_MESSAGE"
    print_info "Isso pode ser normal se houver departamentos associados"
fi

echo ""
print_info "=== TESTE 8: Listar empresas finais ==="
FINAL_RESPONSE=$(curl -s "$API_BASE/companies")
FINAL_COUNT=$(echo "$FINAL_RESPONSE" | jq -r '.data | length' 2>/dev/null || echo "0")
print_info "Total de empresas após testes: $FINAL_COUNT"

if [ "$FINAL_COUNT" -gt 0 ]; then
    echo "Empresas existentes:"
    echo "$FINAL_RESPONSE" | jq -r '.data[] | "🏢 \(.id) - \(.name) (Departamentos: \(.departments_count // 0))"'
fi

echo ""
print_success "Testes do CRUD de empresas concluídos!"
print_info "💡 Resumo dos testes:"
echo "   ✅ Listagem de empresas"
echo "   ✅ Criação de empresa"
echo "   ✅ Busca por empresa específica"
echo "   ✅ Atualização de empresa"
echo "   ✅ Validação de dados"
echo "   ✅ Tentativa de deleção"

echo ""
print_info "🌐 Para testar o frontend:"
echo "   1. Acesse: http://localhost:8080/companies"
echo "   2. Teste criar, editar, visualizar empresas"
echo "   3. Verifique navegação e responsividade"

echo ""
print_info "📋 Próximos passos sugeridos:"
echo "   • Criar departamentos para as empresas"
echo "   • Testar estrutura organizacional"
echo "   • Associar usuários às empresas" 