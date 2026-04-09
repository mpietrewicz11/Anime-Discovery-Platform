#!/bin/bash

echo "Starting build process..."

PROJECT_DIR="/home/IT-490-Project"
WEB_DIR="/var/www/html"
BACKEND_DIR="$PROJECT_DIR/backend"
WEBSERVER_DIR="$PROJECT_DIR/webserver"

echo "Going to project directory..."
cd $PROJECT_DIR  exit

echo "Pulling latest changes from Git..."
git pull origin main

echo "Installing PHP dependencies..."
cd $BACKEND_DIR  exit
composer install

echo "Copying web files to Apache directory..."
sudo cp -r $WEBSERVER_DIR/* $WEB_DIR/

echo "Setting permissions..."
sudo chown -R www-data:www-data $WEB_DIR
sudo chmod -R 755 $WEB_DIR

echo "Restarting Apache..."
sudo systemctl restart apache2

echo "Restarting backend worker..."
sudo systemctl restart it490-backend.service

echo "Build complete."
