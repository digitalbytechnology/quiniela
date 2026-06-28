#!/bin/bash
set -e

# Strip carriage returns from environment variables (fix for CRLF issues from Render dashboard)
export APP_URL=$(printf '%s' "${APP_URL}" | tr -d '\r')
export APP_KEY=$(printf '%s' "${APP_KEY}" | tr -d '\r')
export APP_ENV=$(printf '%s' "${APP_ENV}" | tr -d '\r')
export APP_DEBUG=$(printf '%s' "${APP_DEBUG}" | tr -d '\r')
export DB_CONNECTION=$(printf '%s' "${DB_CONNECTION}" | tr -d '\r')
export DB_HOST=$(printf '%s' "${DB_HOST}" | tr -d '\r')
export DB_PORT=$(printf '%s' "${DB_PORT}" | tr -d '\r')
export DB_DATABASE=$(printf '%s' "${DB_DATABASE}" | tr -d '\r')
export DB_USERNAME=$(printf '%s' "${DB_USERNAME}" | tr -d '\r')
export DB_PASSWORD=$(printf '%s' "${DB_PASSWORD}" | tr -d '\r')
export LOG_CHANNEL=$(printf '%s' "${LOG_CHANNEL}" | tr -d '\r')

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Clearing caches (routes, config, views)..."
php artisan route:clear
php artisan config:clear
php artisan view:clear

echo "==> Starting Apache..."
exec apache2-foreground
