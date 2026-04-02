#!/bin/bash

set -e

echo "Deploying webserver files..."

sudo rm -rf /var/www/html/*
sudo cp -r /home/ae396/IT-490-Project/webserver/* /var/www/html/
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo systemctl restart apache2

echo "Web deployment complete."