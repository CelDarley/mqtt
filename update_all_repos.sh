#!/bin/bash

# Script para atualizar todos os repositórios do projeto MQTT IoT
# Criado em: $(date +"%Y-%m-%d %H:%M:%S")

echo "🚀 Atualizando todos os repositórios do projeto MQTT IoT..."
echo "=============================================="

# Definir mensagem de commit
COMMIT_MESSAGE="feat: implementação completa do sistema IoT MQTT

- ✅ Nova abordagem simplificada do captive portal
- ✅ Campo MAC visível read-only no app mobile  
- ✅ Validação JavaScript robusta
- ✅ Debug aprimorado com logs detalhados
- ✅ Interface melhorada com feedback visual
- ✅ Sistema completo de documentação
- ✅ Correções de redirecionamento
- ✅ Fluxo otimizado captive portal → app → web"

# Função para atualizar um repositório
update_repo() {
    local repo_name=$1
    local repo_path=$2
    
    echo ""
    echo "📁 Atualizando repositório: $repo_name"
    echo "----------------------------------------"
    
    if [ -d "$repo_path" ]; then
        cd "$repo_path"
        
        # Verificar se é um repositório Git
        if [ -d ".git" ]; then
            echo "📋 Status atual:"
            git status --short
            
            echo ""
            echo "➕ Adicionando todos os arquivos..."
            git add .
            
            echo "💾 Fazendo commit..."
            git commit -m "$COMMIT_MESSAGE"
            
            echo "📤 Fazendo push para GitHub..."
            git push origin main
            
            if [ $? -eq 0 ]; then
                echo "✅ $repo_name atualizado com sucesso!"
            else
                echo "❌ Erro ao fazer push do $repo_name"
                echo "🔧 Tentando com 'master' branch..."
                git push origin master
                
                if [ $? -eq 0 ]; then
                    echo "✅ $repo_name atualizado com sucesso (branch master)!"
                else
                    echo "❌ Erro persistente no $repo_name - verifique manualmente"
                fi
            fi
        else
            echo "❌ $repo_path não é um repositório Git válido"
        fi
        
        cd ..
    else
        echo "❌ Diretório $repo_path não encontrado"
    fi
}

# Atualizar cada repositório
echo "🎯 Iniciando atualização dos 3 repositórios..."

update_repo "MQTT Backend" "mqtt"
update_repo "IoT Config Web Laravel" "iot-config-web-laravel"  
update_repo "IoT Config App Laravel" "iot-config-app-laravel"

echo ""
echo "=============================================="
echo "🎉 Processo de atualização concluído!"
echo ""
echo "📊 Resumo das alterações principais:"
echo "- 🔧 ESP32 firmware com nova abordagem"
echo "- 📱 App mobile com campo MAC visível"
echo "- 🌐 Interface web otimizada"
echo "- 📋 Documentação completa"
echo "- 🐛 Correções de bugs e melhorias"
echo ""
echo "🔗 Verifique seus repositórios no GitHub!"
echo "==============================================" 