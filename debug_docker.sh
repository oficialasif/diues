#!/bin/bash

# Debug script for Docker container
echo "🔍 Docker Container Debug Script"
echo "================================"

echo ""
echo "📁 Current working directory:"
pwd

echo ""
echo "📂 Contents of /var/www/html:"
ls -la /var/www/html/

echo ""
echo "📂 Contents of /var/www/html/backend:"
ls -la /var/www/html/backend/

echo ""
echo "🐘 PHP version:"
php --version

echo ""
echo "🔌 PHP extensions:"
php -m | grep -E "(pdo|pgsql)"

echo ""
echo "🌐 Apache configuration:"
apache2ctl -S

echo ""
echo "📄 Apache sites enabled:"
ls -la /etc/apache2/sites-enabled/

echo ""
echo "📄 Apache default site config:"
cat /etc/apache2/sites-available/000-default.conf

echo ""
echo "✅ Debug complete!"
