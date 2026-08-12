#!/bin/sh
set -e

# Render (and most PaaS Docker hosts) assign the listen port via $PORT at runtime;
# docker-compose.prod.yml sets it explicitly for the Droplet. Only ${PORT} is
# substituted here — see the template for why that matters.
export PORT="${PORT:-10000}"
envsubst '${PORT}' < /etc/nginx/templates/app.conf.template > /etc/nginx/conf.d/default.conf

exec "$@"
