#!/bin/bash
# start_laravel.sh — Start Laravel dev server for Tailscale access
# Usage:  chmod +x start_laravel.sh && ./start_laravel.sh

cd /c/Users/Admin/Desktop/cbt-laravel-sims-updated/cbt-laravel

echo ">>> Checking .env file..."
if [ ! -f .env ]; then
    echo "ERROR: .env not found!"
    exit 1
fi

echo ">>> Starting Laravel dev server on 0.0.0.0:8000 ..."
php artisan serve --host=0.0.0.0 --port=8000 --no-reload
