#!/bin/bash
# Use Railway's PORT or default to 8080
PORT="${PORT:-8080}"

# Configure Apache to listen on the correct port at runtime
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Start Apache
exec apache2-foreground
