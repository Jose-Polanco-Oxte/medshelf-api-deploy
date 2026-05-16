#!/bin/sh
set -e

echo "Iniciando Laravel en Railway..."

# Optimizar Laravel para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones automáticamente en cada deploy
echo "Ejecutando migraciones..."
php artisan migrate --force

echo "Listo. Arrancando servicios..."

# Iniciar supervisor (FrankenPHP + Queue Workers)
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
