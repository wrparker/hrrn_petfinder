#!/bin/bash

if [ -f "/var/www/html/wordpress_config_complete" ]; then echo "Config exists, not running"; exit 0; fi

cd /var/www/html/ && wp config create --allow-root  --dbhost=db --dbname=wordpress --dbuser=wordpress --dbpass=wordpress
cd /var/www/html/ && wp core install --url=localhost:8000 --title="WP Dev" --admin_name=admin --admin_password=admin --admin_email=you@example.com --skip-email --allow-root
wp plugin install https://downloads.wordpress.org/plugin/all-in-one-wp-migration.7.11.zip --allow-root
wp plugin activate all-in-one-wp-migration --allow-root
touch wordpress_config_complete