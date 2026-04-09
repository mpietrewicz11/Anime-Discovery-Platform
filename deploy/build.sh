#!/bin/bash

set -e

LOGFILE="/home/ae396/anime_build.log"
exec > >(tee -a "$LOGFILE") 2>&1

PROJECT_DIR="/home/ae396/git/IT-490-Project"
WEB_SRC="$PROJECT_DIR/webserver"
BACKEND_SRC="$PROJECT_DIR/backend"
WEB_DEST="/var/www/html"

echo " Build started at $(date) "

if [ ! -d "$PROJECT_DIR" ]; then
    echo "Project directory not found: $PROJECT_DIR"
    exit 1
fi

cd "$PROJECT_DIR"

echo "1 Pulling latest code..."
git pull origin main

echo "2 Installing backend dependencies..."
cd "$BACKEND_SRC"
composer install --no-interaction

echo "3 Deploying web files..."
sudo rm -rf "$WEB_DEST"/*
sudo cp -r "$WEB_SRC"/* "$WEB_DEST"/

echo "4 Setting ownership and permissions..."
sudo chown -R www-data:www-data "$WEB_DEST"
sudo find "$WEB_DEST" -type d -exec chmod 755 {} ;
sudo find "$WEB_DEST" -type f -exec chmod 644 {} ;

echo "5 Restarting Apache..."
sudo systemctl restart apache2

echo "6 Restarting project services..."
if systemctl list-unit-files | grep -q "^it490-backend.service"; then
    sudo systemctl restart it490-backend.service
else
    echo "it490-backend.service not found, skipping..."
fi

if systemctl list-unit-files | grep -q "^it490-deploy.service"; then
    sudo systemctl restart it490-deploy.service
else
    echo "it490-deploy.service not found, skipping..."
fi

echo " Build finished successfully at $(date)"
