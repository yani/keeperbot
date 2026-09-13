FROM alpine:3.24.1

# Install necessary packages
RUN apk update && apk add --no-cache \
    php85 \
    php85-mbstring \
    php85-curl \
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