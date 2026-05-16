release: php artisan migrate --force && php artisan l5-swagger:generate
web: php artisan config:cache && php artisan route:cache && php artisan event:cache && frankenphp php-server -r public/