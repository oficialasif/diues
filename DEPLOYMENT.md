# 🚀 Deployment Guide - DIU Esports Community Portal

This guide covers deploying the DIU Esports Community Portal to various hosting platforms.

## 📋 Pre-deployment Checklist

- [ ] All sensitive files are in `.gitignore`
- [ ] Configuration templates are created
- [ ] Database schema is ready
- [ ] Environment variables are configured
- [ ] File permissions are set correctly

## 🌐 Hosting Options

### 1. Shared Hosting (cPanel, Plesk)

#### Frontend Deployment
```bash
# Build the project
npm run build

# Upload the following directories to your hosting:
# - .next/
# - public/
# - package.json
# - package-lock.json
# - next.config.js
```

#### Backend Deployment
```bash
# Upload backend/ directory to your hosting
# Ensure PHP 8.0+ is available
# Configure database credentials
```

#### Configuration Steps
1. **Database Setup**
   - Create MySQL database
   - Import `backend/config/schema.sql`
   - Update `backend/config/database.php`

2. **File Permissions**
   ```bash
   # Set upload directory permissions
   chmod 755 backend/uploads/
   chmod 755 backend/uploads/photos/
   chmod 755 backend/uploads/posters/
   chmod 755 backend/uploads/icons/
   chmod 755 backend/uploads/highlights/
   chmod 755 backend/uploads/logos/
   ```

3. **URL Configuration**
   - Update API base URL in frontend
   - Configure subdomain or subdirectory routing

### 2. VPS/Dedicated Server

#### Server Requirements
- **OS**: Ubuntu 20.04+ / CentOS 8+
- **Web Server**: Nginx or Apache
- **PHP**: 8.0+ with extensions
- **MySQL**: 8.0+ or MariaDB 10.5+
- **Node.js**: 18+ (for frontend)

#### Installation Steps

1. **Update System**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Install LAMP Stack**
   ```bash
   sudo apt install apache2 mysql-server php php-mysql php-pdo php-mbstring php-xml php-curl php-gd php-zip
   ```

3. **Install Node.js**
   ```bash
   curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
   sudo apt-get install -y nodejs
   ```

4. **Configure Apache/Nginx**
   ```apache
   # Apache Virtual Host
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /var/www/diuecport
       
       <Directory /var/www/diuecport>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

5. **Deploy Application**
   ```bash
   # Clone repository
   git clone https://github.com/yourusername/diuecport.git /var/www/diuecport
   
   # Set permissions
   sudo chown -R www-data:www-data /var/www/diuecport
   sudo chmod -R 755 /var/www/diuecport
   
   # Install frontend dependencies
   cd /var/www/diuecport
   npm install
   npm run build
   ```

### 3. Cloud Platforms

#### Vercel (Frontend)
```bash
# Install Vercel CLI
npm i -g vercel

# Deploy
vercel

# Or connect GitHub repository for automatic deployments
```

#### Netlify (Frontend)
```bash
# Build command
npm run build

# Publish directory
.next

# Environment variables in Netlify dashboard
```

#### Railway (Full Stack)
```bash
# Connect GitHub repository
# Railway will auto-detect and deploy
# Configure environment variables in dashboard
```

#### Heroku (Full Stack)
```bash
# Create Procfile
web: npm start

# Set buildpacks
heroku buildpacks:set heroku/nodejs
heroku buildpacks:add heroku/php

# Deploy
git push heroku main
```

## 🔧 Configuration Files

### Frontend Environment
Create `.env.local`:
```env
NEXT_PUBLIC_API_BASE_URL=https://yourdomain.com/backend/api
NEXT_PUBLIC_SITE_URL=https://yourdomain.com
```

### Backend Configuration
Update `backend/config/database.php`:
```php
private $host = 'your-db-host';
private $db_name = 'your-database-name';
private $username = 'your-username';
private $password = 'your-password';
```

### Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ backend/api/index.php?path=$1 [QSA,L]
```

#### Nginx
```nginx
location /api/ {
    try_files $uri $uri/ /backend/api/index.php?$query_string;
}

location /backend/ {
    try_files $uri $uri/ /backend/index.php?$query_string;
}
```

## 🔒 Security Configuration

### SSL/HTTPS
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d yourdomain.com
```

### File Permissions
```bash
# Secure sensitive files
chmod 600 backend/config/database.php
chmod 600 backend/config/auth.php

# Upload directory permissions
chmod 755 backend/uploads/
chmod 644 backend/uploads/*.jpg
```

### Database Security
```sql
-- Create dedicated database user
CREATE USER 'diuecport_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON diuecport.* TO 'diuecport_user'@'localhost';
FLUSH PRIVILEGES;
```

## 📊 Performance Optimization

### Frontend
```bash
# Enable compression
npm run build
npm run start

# Use CDN for static assets
# Enable caching headers
```

### Backend
```php
// Enable OPcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
```

### Database
```sql
-- Add indexes for performance
CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_tournaments_status ON tournaments(status);
CREATE INDEX idx_committee_year ON committee(year);
```

## 🚨 Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check PHP error logs
   - Verify file permissions
   - Check database connection

2. **404 Not Found**
   - Verify URL rewriting configuration
   - Check file paths
   - Ensure .htaccess is present

3. **Database Connection Failed**
   - Verify database credentials
   - Check MySQL service status
   - Verify database exists

4. **Upload Failures**
   - Check directory permissions
   - Verify PHP upload settings
   - Check file size limits

### Debug Mode
```php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Log Files
```bash
# Check Apache logs
sudo tail -f /var/log/apache2/error.log

# Check PHP logs
sudo tail -f /var/log/php8.0-fpm.log

# Check MySQL logs
sudo tail -f /var/log/mysql/error.log
```

## 📈 Monitoring

### Health Checks
```bash
# Create health check endpoint
curl https://yourdomain.com/api/health

# Monitor response times
curl -w "@curl-format.txt" -o /dev/null -s "https://yourdomain.com"
```

### Backup Strategy
```bash
# Database backup
mysqldump -u username -p database_name > backup.sql

# File backup
tar -czf uploads_backup.tar.gz backend/uploads/

# Automated backup script
0 2 * * * /path/to/backup_script.sh
```

## 🔄 CI/CD Pipeline

### GitHub Actions
```yaml
name: Deploy
on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Deploy to server
        run: |
          # Add deployment steps
```

## 📞 Support

For deployment issues:
- Check server error logs
- Verify configuration files
- Test database connectivity
- Review file permissions

---

**Happy Deploying! 🚀**
