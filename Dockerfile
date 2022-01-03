FROM php:8.0-apache
ENV VERSION=1.0

EXPOSE 80

SHELL ["/bin/bash", "-c"]

RUN apt-get update -qq
RUN apt-get install -qy \
        gnupg \
        gettext-base \
        iputils-ping \
        libicu-dev \
        libzip-dev \
        unzip \
        zip \
        jq \
        default-mysql-client \
        libpng-dev \
        zlib1g-dev  \
        wait-for-it \
        zsh \
        sudo \
        vim \
        cron \
        procps \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP Extensions
RUN	docker-php-ext-install -j$(nproc) intl opcache pdo_mysql mysqli zip gd
RUN pecl install apcu && docker-php-ext-enable apcu
RUN pecl install uopz && docker-php-ext-enable uopz
RUN rm /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini
RUN rm /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

RUN mkdir /app && mkdir /www && chown www-data: /www && chown www-data: /app

# Apcache settings
COPY cockpit/cockpit.conf /etc/apache2/sites-available/000-default.conf
COPY cockpit/apache.conf /etc/apache2/conf-available/app.conf

RUN a2enconf app
RUN a2enmod rewrite remoteip proxy proxy_http
RUN a2disconf other-vhosts-access-log

# Cockpit View
COPY --chown=www-data View/dist /app/view
RUN if [[ -z $(ls /app/view/) ]]; \
    then curl https://repo.sentinelo.com/repository/CockpitCommunityEdition/view.tar | tar xv --no-same-owner -C /app/ >> /dev/null\
    && rm -rf /app/view  \
    && mv /app/dist /app/view ;\
    fi
RUN ln -s /app/view/ /www/view

# Cockpit Admin
COPY --chown=www-data Admin/dist /app/admin
RUN if [[ -z $(ls /app/admin/) ]]; \
    then curl https://repo.sentinelo.com/repository/CockpitCommunityEdition/admin.tar | tar xv --no-same-owner -C /app/ >> /dev/null\
    && rm -rf /app/admin  \
    && mv /app/dist /app/admin ;\
    fi
RUN ln -s /app/admin/ /www/admin

# Cockpit Studio
COPY --chown=www-data Studio/dist /app/studio
RUN if [[ -z $(ls /app/studio/) ]]; \
    then curl https://repo.sentinelo.com/repository/CockpitCommunityEdition/studio.tar | tar xv --no-same-owner -C /app/ >> /dev/null\
    && rm -rf /app/studio  \
    && mv /app/dist /app/studio ;\
    fi
RUN ln -s /app/studio/ /www/studio

# Cockpit core
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY --chown=www-data Core/ /app/core/
RUN ln -s /app/core/public /www/core
USER www-data
RUN --mount=type=cache,target=/tmp/cache cd /app/core && \
     composer install \
      --no-ansi \
      --no-interaction \
      --no-plugins \
      --no-progress \
      --no-scripts \
      --optimize-autoloader
COPY --chown=www-data cockpit/.env /app/core
COPY --chown=www-data cockpit/createCockpitDB.sh /app/core

WORKDIR /app/core

##############


