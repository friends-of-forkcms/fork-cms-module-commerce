FROM php:7.4-apache

# Run Apache with port 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf
ENV PORT=8080

# Install GD2
RUN apt-get update && apt-get install -y --no-install-recommends --allow-downgrades \
    libonig-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libz-dev \
    zlib1g-dev \
    libpng-dev && \
    docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
    docker-php-ext-install -j$(nproc) gd && \
    rm -rf /var/lib/apt/lists/*

# Install pdo_mysql
RUN docker-php-ext-install pdo_mysql

# Install mbstring
RUN docker-php-ext-install mbstring

# Install zip & unzip
RUN apt-get update && apt-get install -y libzip-dev zip && \
    docker-php-ext-install zip && \
    rm -rf /var/lib/apt/lists/*

# Install intl
RUN apt-get update && apt-get install -y --no-install-recommends \
    g++ \
    libicu-dev \
    zlib1g-dev && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl && \
    rm -rf /var/lib/apt/lists/*

# Install MariaDB in our fat testcontainer
RUN apt-get update && apt-get install -y --no-install-recommends --allow-downgrades \
    mariadb-client mariadb-server && \
    rm -rf /var/lib/apt/lists/* && \
    echo '[mysqld]\nbind_address = 0.0.0.0' > /etc/mysql/conf.d/mysql.cnf

# Install yq
RUN apt-get update && apt-get install -y wget && \
    wget -O /usr/local/bin/yq https://github.com/mikefarah/yq/releases/download/1.15.0/yq_linux_amd64 && \
    chmod 777 /usr/local/bin/yq

# Install composer
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

# Set the work directory to /var/www/html so all subsequent commands in this file start from that directory.
# Also set this work directory so that it uses this directory everytime we use docker exec.
WORKDIR /var/www/html

# Clone Fork CMS codebase as base to test our module
RUN curl -sL https://github.com/forkcms/forkcms/archive/5.9.2.tar.gz | tar xz --strip-components 1

# Install composer dependencies
RUN composer require php "^7.4" && \
    composer require tetranz/select2entity-bundle "v2.10.1" && \
    composer require knplabs/knp-snappy-bundle "v1.6.0" && \
    composer require h4cc/wkhtmltopdf-amd64 "^0.12.4" && \
    composer require gedmo/doctrine-extensions "^3.0" && \
    composer require jeroendesloovere/sitemap-bundle "^2.0" && \
    composer require --dev doctrine/doctrine-fixtures-bundle

# Install the composer dependencies
RUN composer install --prefer-dist --no-dev --no-scripts --no-progress

# Copy our module files into the container.
COPY deploy /var/www/html/deploy
# COPY . /var/www/html

# Give apache write access to host
RUN chown -R www-data:www-data /var/www/html

# This specifies on which port the application will run. This is pure communicative and makes this 12 factor app compliant
# (see https://12factor.net/port-binding).
EXPOSE 80 443

# Define our entrypoint script that will setup the container when it boots up
ENTRYPOINT ["deploy/docker-entrypoint.sh"]
