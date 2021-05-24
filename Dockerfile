# The php:7.3-apache Docker image is based on debian:buster.

FROM php:7.3-apache-buster

RUN apt-get update && apt-get install -y libsodium-dev
RUN pecl install -f libsodium

RUN apt-get update \
    && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        git \
        unzip \
        wget \
        xz-utils \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-install -j$(nproc) exif \
    && docker-php-ext-install -j$(nproc) sodium

RUN wget https://johnvansickle.com/ffmpeg/releases/ffmpeg-release-amd64-static.tar.xz \
      && tar Jxvf ./ffmpeg-release-amd64-static.tar.xz \
      && cp ./ffmpeg*amd64-static/ffmpeg /usr/local/bin/

# Install Composer and make it available in the PATH
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Set the WORKDIR to /app so all following commands run in /app
WORKDIR /var/www/html

COPY . ./

# Install dependencies with Composer.
RUN composer install --prefer-source --no-interaction
