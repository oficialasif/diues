# 🐳 Docker Deployment Guide for DIU Esports Backend

## 🎯 Why Docker?

Since Render doesn't have a direct PHP runtime option, Docker gives us:
- ✅ **Full control** over PHP environment
- ✅ **Consistent deployment** across environments
- ✅ **All PHP extensions** we need (PostgreSQL, etc.)
- ✅ **Apache web server** for better performance
- ✅ **Easy debugging** and troubleshooting

## 🚀 Deploy with Docker - 3 Simple Steps

### Step 1: Create Web Service
1. Go to [render.com](https://render.com)
2. Click "New +" → "Web Service"
3. Connect your GitHub repo: `oficialasif/diuesports`

### Step 2: Configure Docker Service
- **Name**: `diu-esports-backend`
- **Root Directory**: `.` (repository root - **NOT** backend)
- **Runtime**: `Docker` ⚠️ **MUST SELECT DOCKER**
- **Dockerfile Path**: `./Dockerfile` (auto-detected)

### Step 3: Set Environment Variables
Copy all variables from `RENDER_ENVIRONMENT_VARS.md`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://diu-esports-backend.onrender.com
FRONTEND_URL=https://diues.vercel.app
DB_DRIVER=pgsql
DB_HOST=dpg-d2qcflre5dus73bt42b0-a.oregon-postgres.render.com
DB_NAME=diu_esports_db
DB_USERNAME=diu_esports_user
DB_PASSWORD=N9P2tK3xOtsOKnpZqrk1PmtTPO34eFrA
DB_PORT=5432
STORAGE_TYPE=local
UPLOAD_PATH=uploads/
MAX_FILE_SIZE=5242880
ALLOWED_FILE_TYPES=image/jpeg,image/png,image/gif,image/webp
CORS_ALLOWED_ORIGINS=https://diues.vercel.app,http://localhost:3000
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=Strict
LOG_LEVEL=error
TIMEZONE=UTC
```

## 🐳 What the Dockerfile Does

### Base Image
- **PHP 8.1** with Apache web server
- **Latest stable** PHP version
- **Production-ready** configuration

### Installed Extensions
- `pdo` & `pdo_pgsql` - Database connectivity
- `pgsql` - PostgreSQL support
- `zip` - File handling
- `mbstring` - String handling

### File Structure
```
/var/www/html/          # Web root
├── api/                # API endpoints
├── admin/              # Admin panel
├── config/             # Configuration files
├── uploads/            # File uploads
└── logs/               # Application logs
```

### Permissions
- **www-data** user for security
- **755** permissions for directories
- **Apache mod_rewrite** enabled

## 🔧 Alternative: Use Blueprint Deployment

### Option 1: Blueprint (Recommended)
1. Click "New +" → "Blueprint"
2. Connect your GitHub repo
3. Render will automatically use `render.yaml`
4. All settings pre-configured

### Option 2: Manual Docker Service
1. Follow the 3 steps above
2. Set each environment variable manually
3. Wait for Docker build to complete

## 🧪 Test After Deployment

### 1. Health Check
```
https://diu-esports-backend.onrender.com/test_render.php
```
Should show:
- Database connection success
- PHP version info
- Environment variables

### 2. API Test
```
https://diu-esports-backend.onrender.com/api
```
Should show:
- API endpoints list
- Version information

### 3. Database Setup
```
https://diu-esports-backend.onrender.com/install.php
```
Run this to:
- Create database tables
- Insert sample data
- Set up admin user

## 🔍 Docker Build Process

### Build Steps
1. **Base Image**: Pull PHP 8.1 + Apache
2. **Dependencies**: Install system packages
3. **Extensions**: Install PHP extensions
4. **Configuration**: Set up Apache
5. **Files**: Copy backend code
6. **Permissions**: Set file permissions
7. **Start**: Launch Apache server

### Build Time
- **First build**: 5-10 minutes
- **Subsequent builds**: 2-5 minutes
- **Dependencies cached** for faster builds

## 🚨 Troubleshooting Docker

### Common Issues

#### 1. Build Fails
- **Problem**: Docker build error
- **Solution**: Check Dockerfile syntax, ensure all files exist

#### 2. Service Won't Start
- **Problem**: Container exits immediately
- **Solution**: Check logs, verify environment variables

#### 3. Database Connection Failed
- **Problem**: Can't connect to PostgreSQL
- **Solution**: Verify database credentials and network access

#### 4. File Permissions
- **Problem**: Can't write to uploads/logs
- **Solution**: Check Dockerfile permissions setup

### Debug Commands

```bash
# Check container logs
docker logs <container_id>

# Enter container
docker exec -it <container_id> bash

# Check PHP extensions
php -m

# Check Apache status
service apache2 status
```

## 📱 Final URLs

After successful deployment:

- **Backend**: https://diu-esports-backend.onrender.com
- **API**: https://diu-esports-backend.onrender.com/api
- **Admin**: https://diu-esports-backend.onrender.com/admin
- **Health Check**: https://diu-esports-backend.onrender.com/test_render.php

## 🎯 Success Indicators

- ✅ Docker build completes successfully
- ✅ Service starts without errors
- ✅ Health check responds
- ✅ Database connection established
- ✅ API endpoints accessible
- ✅ Frontend can communicate

## 🔄 Updates and Redeployment

### Automatic Deployment
- **Git push** triggers automatic rebuild
- **Docker image** rebuilt with latest code
- **Environment variables** preserved

### Manual Redeploy
1. Go to service dashboard
2. Click **Manual Deploy**
3. Select branch/commit
4. Wait for build completion

---

**🎯 Docker gives you full control and reliability! Your backend will work perfectly.**
