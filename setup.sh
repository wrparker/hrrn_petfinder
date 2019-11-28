#!/bin/bash
# use this to get a wordpress installation for phpstorm in this directory for code complete.
mkdir wordpress
cd wordpress
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
php wp-cli.phar core download --allow-root