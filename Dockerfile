FROM php:7.4-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install mysql and mysqladmin binaries
RUN apt-get update && apt-get install -y --no-install-recommends mariadb-client
COPY --from=mariadb:10 /usr/bin/mysqladmin /usr/local/bin/mysqladmin

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

# Install pdo_mysql & mbstring
RUN docker-php-ext-install pdo_mysql mbstring

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

# Install yq (a YAML processor). We need this to configure our parameters.yml
RUN apt-get update && apt-get install -y wget && \
    wget -O /usr/local/bin/yq https://github.com/mikefarah/yq/releases/download/1.15.0/yq_linux_amd64 && \
    chmod 777 /usr/local/bin/yq

# Install Composer 2
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

# Set the work directory to /var/www/html so all subsequent commands in this file start from that directory.
# Also set this work directory so that it uses this directory everytime we use docker exec.
WORKDIR /var/www/html

# Download Fork CMS as base to test our module
#RUN curl -sL https://github.com/forkcms/forkcms/archive/5.9.2.tar.gz | tar xz --strip-components 1
RUN curl -sL https://github.com/jessedobbelaere/forkcms/archive/add-module-installation-command.tar.gz | tar xz --strip-components 1

# Install the Fork CMS composer dependencies
RUN composer install --prefer-dist --no-dev --no-scripts --no-progress

# Copy our repository files into the container.
COPY . /var/www/html

# Give apache user write access
RUN chown -R www-data:www-data /var/www/html

# This specifies on which port the application will run. This is pure communicative and makes this 12 factor app compliant
# (see https://12factor.net/port-binding).
EXPOSE 80 443

# Define our entrypoint script that will setup the container when it boots up
ENTRYPOINT ["deploy/docker-entrypoint.sh"]
