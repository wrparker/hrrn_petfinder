FROM phpstorm/php-71-apache-xdebug-26
RUN apt update && apt install -y default-mysql-client
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x wp-cli.phar && mv wp-cli.phar /usr/local/bin/wp
RUN cd /var/www/html && wp core download --allow-root
COPY .htaccess /var/www/html

RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html
