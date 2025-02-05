FROM alpine:3.21

# Install necessary packages
RUN apk update && apk add --no-cache \
    php83 \
    php83-mbstring \
    php83-curl \
    php83-json \
    curl \
    composer

# Copy application files
COPY . /var/keeperbot

# Change working directory
WORKDIR /var/keeperbot

# Setup default .env file
RUN cp .env.example .env

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Start the bot
CMD ["php", "./keeperbot.php"]