#!/bin/sh
set -eu

cd /var/www/html

mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if [ "${APP_ENV:-production}" = local ] && [ -z "${APP_KEY:-}" ]; then
    if [ ! -s storage/app/private/.docker-app-key ]; then
        (umask 077; php -r 'echo "base64:".base64_encode(random_bytes(32));' > storage/app/private/.docker-app-key)
    fi
    APP_KEY=$(cat storage/app/private/.docker-app-key)
    export APP_KEY
fi

if [ -z "${APP_KEY:-}" ]; then
    echo 'APP_KEY must be supplied and retained across deployments.' >&2
    exit 1
fi

exec "$@"
