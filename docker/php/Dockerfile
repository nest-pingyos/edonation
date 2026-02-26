FROM php:8.2-apache

# ติดตั้ง PHP extensions ที่จำเป็น (mysqli, pdo_mysql, gd)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd

# เปิดใช้งาน Apache modules ที่จำเป็น
RUN a2enmod rewrite headers

# ตั้งค่าสิทธิ์การเข้าถึงไฟล์
WORKDIR /var/www/html/edonation

EXPOSE 80
