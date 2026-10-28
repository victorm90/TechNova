#!/usr/bin/env bash
set -e

cd "$(dirname "$0")/.."

echo "Instalando extensiones PHP necesarias..."

EXTENSIONS=""

for ext in mbstring zip intl bcmath gd pdo_sqlite; do
    if ! php -m | grep -qi "^${ext}$"; then
        EXTENSIONS="${EXTENSIONS} ${ext}"
    fi
done

if [ -n "${EXTENSIONS}" ]; then
    sudo apt-get update
    sudo apt-get install -y --no-install-recommends \
        libonig-dev \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev

    sudo docker-php-ext-configure gd --with-freetype --with-jpeg
    sudo docker-php-ext-install -j"$(nproc)" ${EXTENSIONS}
fi

echo "Preparando el entorno de la aplicación..."

if [ ! -f .env ]; then
    cp .env.example .env
fi

sed -i 's/^APP_NAME=.*/APP_NAME=TechNova/' .env
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env

php artisan key:generate --force

touch database/database.sqlite

composer install --no-interaction --prefer-dist
npm ci --no-audit --no-fund
npm run build

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link >/dev/null 2>&1 || true

echo "Listo. La aplicación arrancará en el puerto 8000."
