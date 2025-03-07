FROM php:8.3-fpm

RUN apt-get update \
        && apt-get install -y openssh-client \
		&& apt-get install -y \
        unzip \
        zip \
        libzip-dev \
        curl \
        git \
        wget

RUN docker-php-ext-install pdo && \
    docker-php-ext-install pdo_mysql

RUN docker-php-ext-configure zip && \
    docker-php-ext-install zip

RUN #apt-get update && apt-get install -y curl python-is-python3 pkg-config build-essential libasound2 libatk-bridge2.0-0 libatk1.0-0 libatspi2.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libgbm1 libgcc1 libglib2.0-0 libnspr4 libnss3 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libuuid1 libx11-6 libx11-xcb1 libxcb-dri3-0 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxkbcommon0 libxrandr2 libxrender1 libxshmfence1 libxss1 libxtst6 libglib2.0-0

#RUN apt-get install -y nodejs \
#                    npm \
#                    libnode-dev

RUN #npm config set registry http://registry.npmjs.org
RUN #rm -rf ~/.npm && mkdir ~/.npm
RUN #npm install -g puppeteer

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/newgenParser