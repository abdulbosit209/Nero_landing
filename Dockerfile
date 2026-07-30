# Builds a self-contained image for the Yii 2 landing page app, suitable for any
# container host (Render, Fly.io, Railway, etc.). See render.yaml for the Render
# Blueprint that deploys straight from this file.

FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" intl gd zip mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Installed separately from the rest of the source so this (slow) layer is only
# rebuilt when composer.json/composer.lock actually change.
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize \
    && mkdir -p runtime web/assets \
    && chmod -R 777 runtime web/assets

# Production defaults — override YII_DEBUG/YII_ENV via the platform's env vars if
# ever needed, per web/index.php.
ENV YII_ENV=prod
ENV YII_DEBUG=0

# Render (and most PaaS Docker hosts) assign the listen port via $PORT at runtime.
CMD php -S 0.0.0.0:${PORT:-10000} -t web
