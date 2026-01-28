# =============================================================================
# eDonation PHP Application - Production Dockerfile
# -----------------------------------------------------------------------------
# IMPORTANT: This Dockerfile is designed for CROSS-PLATFORM deployment
# Built on Mac (ARM64) → Deployed to Windows Server (AMD64)
# 
# Build with: docker build --platform linux/amd64 -t edonation:latest .
# =============================================================================

# Base image: PHP 8.2 with Apache (Official image supports multi-platform)
FROM --platform=linux/amd64 php:8.2-apache

# Maintainer
LABEL maintainer="eDonation Dev Team"
LABEL description="eDonation PHP Application - Cross-Platform Docker Image"
LABEL platform="linux/amd64"

# =============================================================================
# Environment Variables
# =============================================================================
ENV APACHE_DOCUMENT_ROOT=/var/www/html
ENV TZ=Asia/Bangkok

# Database defaults (override via docker-compose or runtime)
ENV DB_HOST=db
ENV DB_NAME=donation
ENV DB_USER=edonation
ENV DB_PASS=edonate@FON
ENV DB_CHARSET=utf8mb4

# Application defaults
ENV APP_ENV=production
ENV APP_DEBUG=false

# =============================================================================
# System Dependencies & PHP Extensions
# =============================================================================
RUN apt-get update && apt-get install -y --no-install-recommends \
    # Required for MySQL extensions
    default-mysql-client \
    # Required for GD extension (image processing)
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    # Required for zip extension
    libzip-dev \
    unzip \
    # Required for intl extension
    libicu-dev \
    # Required for curl
    libcurl4-openssl-dev \
    # Time zone data
    tzdata \
    # PDF generation (optional, for TCPDF/FPDI)
    libfontconfig1 \
    # Useful tools
    curl \
    wget \
    vim-tiny \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        intl \
        curl \
        opcache \
        exif \
        bcmath

# =============================================================================
# Apache Configuration
# =============================================================================
# Enable required Apache modules
RUN a2enmod rewrite headers expires deflate

# Configure Apache ServerName to suppress warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configure document root and .htaccess support
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Custom Apache virtual host configuration
COPY <<EOF /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot \${APACHE_DOCUMENT_ROOT}
    
    <Directory \${APACHE_DOCUMENT_ROOT}>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security Headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# =============================================================================
# PHP Configuration (Production-optimized)
# =============================================================================
COPY <<EOF /usr/local/etc/php/conf.d/app.ini
[PHP]
; Timezone
date.timezone = Asia/Bangkok

; Error handling (production)
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; Memory & Execution
memory_limit = 256M
max_execution_time = 60
max_input_time = 60
post_max_size = 50M
upload_max_filesize = 50M
max_file_uploads = 20

; Sessions
session.cookie_httponly = On
session.use_strict_mode = On
session.cookie_samesite = "Strict"

; OPcache (production optimization)
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.validate_timestamps = 0
opcache.revalidate_freq = 0
opcache.fast_shutdown = 1

; MySQLi
mysqli.default_socket = /var/run/mysqld/mysqld.sock

; Character encoding
default_charset = "UTF-8"
EOF

# Create log directory
RUN mkdir -p /var/log/php && chown www-data:www-data /var/log/php

# =============================================================================
# Application Files
# =============================================================================
WORKDIR /var/www/html

# Copy application files
COPY --chown=www-data:www-data . .

# Remove development files from production image
RUN rm -rf \
    .git \
    .gitignore \
    .env \
    .env.* \
    *.md \
    docker-compose*.yml \
    Dockerfile* \
    .dockerignore

# Set proper permissions
RUN find . -type d -exec chmod 755 {} \; \
    && find . -type f -exec chmod 644 {} \; \
    && chown -R www-data:www-data .

# =============================================================================
# Entrypoint Script
# =============================================================================
COPY <<'ENTRYPOINT_SCRIPT' /usr/local/bin/docker-entrypoint.sh
#!/bin/bash
set -e

# Generate .env file from environment variables
echo "# Auto-generated .env from Docker environment" > /var/www/html/.env
echo "APP_ENV=${APP_ENV:-production}" >> /var/www/html/.env
echo "APP_DEBUG=${APP_DEBUG:-false}" >> /var/www/html/.env
echo "" >> /var/www/html/.env
echo "# Database Configuration" >> /var/www/html/.env
echo "DB_HOST=${DB_HOST:-db}" >> /var/www/html/.env
echo "DB_NAME=${DB_NAME:-donation}" >> /var/www/html/.env
echo "DB_USER=${DB_USER:-edonation}" >> /var/www/html/.env
echo "DB_PASS=${DB_PASS:-}" >> /var/www/html/.env
echo "" >> /var/www/html/.env
echo "# URLs (Docker mode)" >> /var/www/html/.env
echo "APP_DOMAIN=${APP_DOMAIN:-http://localhost}" >> /var/www/html/.env
echo "API_DOMAIN=${API_DOMAIN:-http://localhost}" >> /var/www/html/.env
echo "BASE_PATH=${BASE_PATH:-}" >> /var/www/html/.env
echo "API_BASE_PATH=${API_BASE_PATH:-/api}" >> /var/www/html/.env
echo "" >> /var/www/html/.env
echo "# LINE Notify" >> /var/www/html/.env
echo "LINE_TOKEN=${LINE_TOKEN:-}" >> /var/www/html/.env
echo "" >> /var/www/html/.env
echo "# Email" >> /var/www/html/.env
echo "GMAIL_USER=${GMAIL_USER:-}" >> /var/www/html/.env
echo "GMAIL_PASS=${GMAIL_PASS:-}" >> /var/www/html/.env
echo "" >> /var/www/html/.env
echo "# JWT" >> /var/www/html/.env
echo "JWT_SECRET=${JWT_SECRET:-your-secret-key}" >> /var/www/html/.env
echo "JWT_EXPIRE=${JWT_EXPIRE:-86400}" >> /var/www/html/.env

# Set correct permissions
chown www-data:www-data /var/www/html/.env
chmod 640 /var/www/html/.env

echo "✅ Environment configured successfully"
echo "📦 DB_HOST: ${DB_HOST:-db}"
echo "📦 DB_NAME: ${DB_NAME:-donation}"
echo "🚀 Starting Apache..."

# Execute the main command
exec "$@"
ENTRYPOINT_SCRIPT

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# =============================================================================
# Health Check
# =============================================================================
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/api/v1/health || curl -f http://localhost/ || exit 1

# =============================================================================
# Expose & Run
# =============================================================================
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
