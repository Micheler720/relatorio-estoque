# php 7.4 apache
FROM php:8.2-apache

# Instala as dependências necessárias
RUN apt-get update \
  && apt-get install -y libicu-dev \
  && docker-php-ext-install pdo pdo_mysql mysqli intl

# Copie o arquivo de configuração do Apache para o contêiner
COPY ./docker/php/sites-enabled.conf/site.conf /usr/local/apache2/conf/httpd.conf

# Habilite o módulo headers
RUN sed -i '/LoadModule headers_module/s/^#//g' /usr/local/apache2/conf/httpd.conf

# Instalação do Xdebug
RUN pecl install xdebug-3.3.2 && docker-php-ext-enable xdebug


RUN apt-get install -y zsh
RUN apt-get install -y git
RUN sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local