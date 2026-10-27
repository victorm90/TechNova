#!/bin/sh
set -e

cd /var/www/html

mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache

php artisan config:cache

if php artisan route:cache; then
    echo "Rutas cacheadas correctamente."
else
    echo "No se pudieron cachear las rutas; se usará el enrutamiento dinámico."
    php artisan route:clear
fi

php artisan view:cache

echo "Esperando la base de datos y ejecutando migraciones..."

intentos=0
until php artisan migrate --force; do
    intentos=$((intentos + 1))

    if [ "$intentos" -ge 10 ]; then
        echo "No se pudo conectar con la base de datos después de $intentos intentos."
        exit 1
    fi

    sleep 3
done

if [ "${SEED_DEMO_DATA:-false}" = "true" ]; then
    echo "Sembrando datos de demostración..."
    php artisan db:seed --force
fi

php artisan storage:link >/dev/null 2>&1 || true

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

exec apache2-foreground
