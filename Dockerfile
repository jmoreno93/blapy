# Dockerfile
FROM php:8.3-cli

# Instala extensiones necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_sqlite

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copia solo composer.json para aprovechar caché
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist

# Luego copia todo el proyecto
COPY . .

# Asegura que el archivo SQLite exista
RUN mkdir -p database && touch database/database.sqlite

# Expone el puerto del servidor Laravel
EXPOSE 8020

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8020"]
