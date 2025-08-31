# Admin Login Fix Guide

## Issues Identified and Fixed

The admin login system had several issues that have been resolved:

### 1. Database Configuration Issues
- **Problem**: Database configuration was using environment variables that weren't set
- **Fix**: Updated `config/database.php` to use hardcoded values for local development

### 2. Missing Database Schema
- **Problem**: The `users` table was missing the `is_active` column
- **Fix**: Added the missing column and updated the schema

### 3. Missing Admin User
- **Problem**: No admin user existed in the database
- **Fix**: Created admin user with proper credentials

### 4. Authentication Redirect Issues
- **Problem**: Failed authentication was redirecting to wrong pages
- **Fix**: Updated redirect paths in `config/auth.php`

## Quick Fix Steps

### Option 1: Run the Complete Setup Script (Recommended)
```bash
# Navigate to your backend directory
cd backend

# Run the complete setup script
php setup_admin.php
```

This script will:
- Create the database if it doesn't exist
- Create all necessary tables
- Insert sample data
- Create the admin user
- Test the authentication system

### Option 2: Run Individual Fix Scripts
```bash
# Test database connection
php test_connection.php

# Fix admin login issues
php fix_admin_login.php
```

## Admin Credentials

After running the fix scripts, you can login with:

- **Username**: `admin`
- **Password**: `admin123`
- **URL**: `/backend/admin/login.php`

## Manual Database Setup

If you prefer to set up manually:

### 1. Create Database
```sql
CREATE DATABASE IF NOT EXISTS diu_esports CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE diu_esports;
```

### 2. Create Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'moderator') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 3. Create Admin User
```sql
INSERT INTO users (username, email, password_hash, role, is_active) VALUES 
('admin', 'admin@diuesports.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE);
```

## Troubleshooting

### Debug Mode
Add `?debug=1` to the login URL to see debug information:
```
/backend/admin/login.php?debug=1
```

### Common Issues

1. **Database Connection Failed**
   - Check if MySQL/XAMPP is running
   - Verify database credentials in `config/database.php`
   - Ensure database `diu_esports` exists

2. **Tables Don't Exist**
   - Run `php setup_admin.php` to create all tables
   - Check if `config/schema.sql` is accessible

3. **Admin User Not Found**
   - Run the setup script to create the admin user
   - Check if the `users` table exists and has data

4. **Permission Denied**
   - Ensure the `is_active` column exists and is set to `TRUE`
   - Verify the user has `admin` role

## File Structure

```
backend/
├── config/
│   ├── database.php      # Database configuration (FIXED)
│   ├── auth.php          # Authentication system (FIXED)
│   └── schema.sql        # Database schema
├── admin/
│   ├── login.php         # Admin login page (ENHANCED)
│   ├── dashboard.php     # Admin dashboard
│   └── ...
├── setup_admin.php       # Complete setup script (NEW)
├── fix_admin_login.php   # Admin login fix script (NEW)
└── test_connection.php   # Database test script (NEW)
```

## Security Notes

- **Change Default Password**: After first login, change the default password `admin123`
- **Remove Debug Mode**: Remove or comment out the debug section in production
- **Database Security**: Consider using environment variables for production database credentials
- **Session Security**: The system includes 8-hour session timeout and CSRF protection

## Testing

After fixing, test the following:

1. **Database Connection**: Run `php test_connection.php`
2. **Admin Login**: Navigate to `/backend/admin/login.php`
3. **Dashboard Access**: Login and verify dashboard loads
4. **Session Management**: Test logout and session timeout
5. **Role Access**: Verify admin-only pages are accessible

## Support

If you continue to have issues:

1. Check the debug output at `/backend/admin/login.php?debug=1`
2. Review error logs in your web server
3. Verify database permissions and connectivity
4. Run the setup scripts to ensure proper initialization

---

**Note**: This fix guide assumes you're using XAMPP with default MySQL settings (localhost, root, no password). Adjust the database configuration in `config/database.php` if you're using different settings.
