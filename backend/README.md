# DIU Esports Community - Backend

This is the PHP/MySQL backend for the DIU Esports Community website, providing a complete admin panel and API for content management.

## 🚀 Features

- **Secure Authentication System** - Admin login with session management
- **Complete CRUD Operations** - Manage all website content
- **File Upload System** - Handle images, posters, and media files
- **RESTful API** - Frontend integration ready
- **Admin Dashboard** - Modern, responsive interface
- **Security Features** - CSRF protection, input sanitization, SQL injection prevention

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for clean URLs)

## 🛠️ Installation

### 1. Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE diu_esports CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -u root -p diu_esports < config/schema.sql
```

3. Update database credentials in `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'diu_esports';
private $username = 'your_username';
private $password = 'your_password';
```

### 2. File Permissions

Set proper permissions for upload directories:
```bash
chmod 755 uploads/
chmod 755 uploads/posters/
chmod 755 uploads/photos/
chmod 755 uploads/highlights/
chmod 755 uploads/logos/
```

### 3. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api/index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
```nginx
location /backend/api {
    try_files $uri $uri/ /backend/api/index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 🔐 Default Admin Access

- **Username**: `admin`
- **Password**: `admin123`
- **URL**: `/backend/admin/login.php`

**⚠️ Important**: Change the default password immediately after first login!

## 📁 Directory Structure

```
backend/
├── admin/                 # Admin panel files
│   ├── login.php         # Admin login
│   ├── dashboard.php     # Main dashboard
│   ├── logout.php        # Logout script
│   └── [other admin pages]
├── api/                  # API endpoints
│   ├── index.php         # Main API router
│   └── handlers/         # API handlers
│       ├── tournaments.php
│       ├── events.php
│       ├── committee.php
│       ├── gallery.php
│       ├── sponsors.php
│       ├── achievements.php
│       ├── settings.php
│       └── auth.php
├── config/               # Configuration files
│   ├── database.php      # Database connection
│   ├── auth.php          # Authentication system
│   └── schema.sql        # Database schema
├── uploads/              # File uploads
│   ├── posters/          # Tournament posters
│   ├── photos/           # Gallery photos
│   ├── highlights/       # Video highlights
│   └── logos/            # Sponsor logos
└── README.md             # This file
```

## 🌐 API Endpoints

### Base URL: `/backend/api`

#### Tournaments
- `GET /tournaments` - Get all tournaments
- `GET /tournaments/{id}` - Get specific tournament
- `GET /tournaments/upcoming` - Get upcoming tournaments
- `GET /tournaments/ongoing` - Get ongoing tournaments
- `GET /tournaments/completed` - Get completed tournaments
- `POST /tournaments` - Create new tournament
- `PUT /tournaments/{id}` - Update tournament
- `DELETE /tournaments/{id}` - Delete tournament

#### Events
- `GET /events` - Get all events
- `GET /events/{id}` - Get specific event
- `GET /events/upcoming` - Get upcoming events
- `POST /events` - Create new event
- `PUT /events/{id}` - Update event
- `DELETE /events/{id}` - Delete event

#### Committee
- `GET /committee` - Get all committee members
- `GET /committee/current` - Get current committee
- `GET /committee/past` - Get past committees
- `POST /committee` - Add new member
- `PUT /committee/{id}` - Update member
- `DELETE /committee/{id}` - Remove member

#### Gallery
- `GET /gallery` - Get all gallery items
- `GET /gallery/{id}` - Get specific item
- `GET /gallery/category/{category}` - Get by category
- `POST /gallery` - Upload new item
- `PUT /gallery/{id}` - Update item
- `DELETE /gallery/{id}` - Delete item

#### Sponsors
- `GET /sponsors` - Get all sponsors
- `GET /sponsors/{id}` - Get specific sponsor
- `POST /sponsors` - Add new sponsor
- `PUT /sponsors/{id}` - Update sponsor
- `DELETE /sponsors/{id}` - Remove sponsor

#### Settings
- `GET /settings` - Get all site settings
- `GET /settings/{key}` - Get specific setting
- `PUT /settings/{key}` - Update setting

## 🔒 Security Features

- **Session Management** - Secure session handling with timeout
- **CSRF Protection** - Cross-site request forgery prevention
- **Input Sanitization** - All user inputs are sanitized
- **SQL Injection Prevention** - Prepared statements used throughout
- **File Upload Security** - File type validation and secure storage
- **Password Hashing** - Bcrypt password hashing
- **Access Control** - Role-based access control (Admin/Moderator)

## 📱 Admin Panel Features

- **Dashboard** - Overview with statistics and quick actions
- **Content Management** - CRUD operations for all content types
- **File Management** - Upload, organize, and manage media files
- **User Management** - Manage admin users and roles
- **Site Settings** - Configure website content and social links
- **Responsive Design** - Works on all devices

## 🚀 Quick Start

1. **Setup Database**:
   ```bash
   mysql -u root -p < config/schema.sql
   ```

2. **Configure Database**:
   Edit `config/database.php` with your credentials

3. **Access Admin Panel**:
   Navigate to `/backend/admin/login.php`

4. **Login**:
   Use default credentials: `admin` / `admin123`

5. **Start Managing Content**:
   Use the dashboard to add tournaments, events, and other content

## 🔧 Configuration

### Database Settings
Edit `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'diu_esports';
private $username = 'your_username';
private $password = 'your_password';
```

### Upload Settings
Configure upload limits in `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

### Security Settings
Session timeout (in `config/auth.php`):
```php
// Check session timeout (8 hours)
if (time() - $_SESSION['last_activity'] > 28800) {
    $this->logout();
    return false;
}
```

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Check database credentials
   - Ensure MySQL service is running
   - Verify database exists

2. **File Upload Errors**:
   - Check directory permissions
   - Verify upload limits in php.ini
   - Ensure upload directories exist

3. **API 404 Errors**:
   - Check .htaccess configuration
   - Verify mod_rewrite is enabled
   - Check file paths and permissions

4. **Session Issues**:
   - Check PHP session configuration
   - Verify session directory permissions
   - Clear browser cookies

### Debug Mode

Enable error reporting in development:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📚 API Documentation

### Request Format
All API requests should include:
- `Content-Type: application/json` header
- JSON payload for POST/PUT requests

### Response Format
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {...},
    "timestamp": "2024-01-01T00:00:00+00:00"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "data": null,
    "timestamp": "2024-01-01T00:00:00+00:00"
}
```

## 🤝 Contributing

1. Follow PSR-12 coding standards
2. Add proper error handling
3. Include input validation
4. Write meaningful commit messages
5. Test thoroughly before submitting

## 📄 License

This project is part of the DIU Esports Community website.

## 🆘 Support

For technical support or questions:
- Check the troubleshooting section
- Review error logs
- Contact the development team

---

**Built with ❤️ for DIU Esports Community**
