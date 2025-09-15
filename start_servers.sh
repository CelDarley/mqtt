#!/bin/bash
# start_servers.sh - Iniciar todos os servidores

echo "🚀 Iniciando todos os servidores..."

# Verificar se as portas estão livres
for port in 8000 8001 8002; do
    if lsof -i:$port >/dev/null 2>&1; then
        echo "⚠️  Porta $port já está em uso"
        echo "   Para liberar: sudo kill -9 \$(lsof -t -i:$port)"
    fi
done

# Iniciar servidores em background
cd mqtt && php artisan serve --port=8000 &
BACKEND_PID=$!

cd ../iot-config-app-laravel && php artisan serve --port=8001 &
APP_PID=$!

cd ../iot-config-web-laravel && php artisan serve --port=8002 &
WEB_PID=$!

cd ..

echo "✅ Servidores iniciados:"
echo "   Backend (PID: $BACKEND_PID): http://localhost:8000"
echo "   App IoT (PID: $APP_PID): http://localhost:8001"
echo "   Web Admin (PID: $WEB_PID): http://localhost:8002"
echo ""
echo "Para parar todos os servidores:"
echo "   pkill -f 'php artisan serve'"
echo "   ou"
echo "   kill $BACKEND_PID $APP_PID $WEB_PID" 