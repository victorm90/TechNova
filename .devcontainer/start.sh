#!/usr/bin/env bash
set -e

cd "$(dirname "$0")/.."

if [ -n "${CODESPACE_NAME:-}" ] && [ -n "${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-}" ]; then
    export APP_URL="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
    export ASSET_URL="${APP_URL}"
fi

php artisan config:clear >/dev/null 2>&1 || true

pkill -f "artisan serve" >/dev/null 2>&1 || true

nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel-serve.log 2>&1 &

echo "Servidor Laravel iniciado en el puerto 8000."
