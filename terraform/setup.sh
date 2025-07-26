#!/bin/bash

# Update and install dependencies
apt update -y
apt install -y docker.io docker-compose git unzip

# Allow ubuntu user to run Docker
usermod -aG docker ubuntu
newgrp docker

# Clone your Laravel repo
cd /home/ubuntu
git clone https://github.com/Carlos-gitjub/LWA.git
cd YOUR_REPO

# Ensure SQLite DB exists and writable
touch database/database.sqlite
chmod -R 775 database

# Build and start containers
docker-compose up -d --build

# Generate Laravel app key (only once)
docker exec laravel_app php artisan key:generate

