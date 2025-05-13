FROM php:8.1-cli

WORKDIR /app
COPY . .

# Instala extensiones requeridas (pdo, pdo_mysql)
RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Puerto en el que Railway escucha por defecto
EXPOSE 8080

# Usa el servidor embebido de PHP como router
CMD ["php", "-S", "0.0.0.0:8080"]

