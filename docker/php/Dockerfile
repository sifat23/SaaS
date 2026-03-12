FROM php:8.3-fpm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libjpeg-dev \
    libwebp-dev \
    libxpm-dev \
    libmagickwand-dev \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g ${GID} laravel || true \
    && useradd -u ${UID} -g ${GID} -m laravel || true

WORKDIR /var/www/html

COPY . /var/www/html

RUN mkdir -p storage bootstrap/cache \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 775 storage bootstrap/cache

USER laravel

EXPOSE 9000

CMD ["php-fpm"]