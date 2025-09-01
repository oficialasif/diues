# 🚀 Render Deployment Guide for DIU Esports Backend

## 📋 Prerequisites
- ✅ GitHub repository connected to Render
- ✅ Render account created
- ✅ Frontend already deployed on Vercel

## 🗄️ Step 1: Create PostgreSQL Database

1. **Go to Render Dashboard**
   - Visit [render.com](https://render.com) and sign in
   - Click "New +" → "PostgreSQL"

2. **Configure Database**
   - **Name**: `diu-esports-db`
   - **Database**: `diu_esports_db`
   - **User**: `diu_esports_user`
   - **Region**: Choose closest to your users (e.g., Oregon for US)
   - **Plan**: Free (for testing) or Starter (for production)

3. **Save Credentials**
   - Copy the **Internal Database URL**
   - Note down: Host, Database, Username, Password, Port
   - These will be used in environment variables

## 🌐 Step 2: Create Web Service

1. **Create Service**
   - Click "New +" → "Web Service"
   - Connect your GitHub repository: `oficialasif/diuesports`

2. **Configure Service**
   - **Name**: `diu-esports-backend`
   - **Root Directory**: `backend` ⚠️ **CRITICAL**
   - **Runtime**: `PHP` ⚠️ **MUST SELECT PHP**
   - **Build Command**: Leave empty or use `echo "PHP ready"`
   - **Start Command**: `php -S 0.0.0.0:$PORT`

3. **Environment Variables**
   Set these in the Render dashboard:

   ```env
   # Application
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-app-name.onrender.com
   FRONTEND_URL=https://diues.vercel.app
   
   # Database (use values from Step 1)
   DB_DRIVER=pgsql
   DB_HOST=your-postgres-host.render.com
   DB_NAME=diu_esports_db
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   DB_PORT=5432
   
   # File Storage
   STORAGE_TYPE=local
   UPLOAD_PATH=uploads/
   MAX_FILE_SIZE=5242880
   ALLOWED_FILE_TYPES=image/jpeg,image/png,image/gif,image/webp
   
   # CORS & Security
   CORS_ALLOWED_ORIGINS=https://diues.vercel.app,http://localhost:3000
   SESSION_SECURE=true
   SESSION_HTTP_ONLY=true
   SESSION_SAME_SITE=Strict
   
   # Logging
   LOG_LEVEL=error
   TIMEZONE=UTC
   ```

## 🚀 Step 3: Deploy

1. **Click "Create Web Service"**
2. **Wait for deployment** (usually 2-5 minutes)
3. **Check deployment logs** for any errors
4. **Note your service URL**: `https://your-app-name.onrender.com`

## 🧪 Step 4: Test Backend

1. **Health Check**
   - Visit: `https://your-app-name.onrender.com/test_render.php`
   - Should show database connection success

2. **API Test**
   - Visit: `https://your-app-name.onrender.com/api`
   - Should show API endpoints list

3. **Database Setup**
   - Visit: `https://your-app-name.onrender.com/install.php`
   - Run the installation script to create tables

## 🔗 Step 5: Connect Frontend

1. **Go to Vercel Dashboard**
   - Visit [vercel.com](https://vercel.com)
   - Select your `diues` project

2. **Set Environment Variables**
   - Go to Settings → Environment Variables
   - Add these variables:

   ```env
   NEXT_PUBLIC_API_BASE_URL=https://your-app-name.onrender.com/api
   NEXT_PUBLIC_SITE_URL=https://diues.vercel.app
   NEXT_PUBLIC_APP_NAME="DIU Esports Community"
   NEXT_PUBLIC_APP_VERSION="1.0.0"
   NEXT_PUBLIC_APP_DESCRIPTION="Modern esports community portal for Daffodil International University"
   ```

3. **Redeploy Frontend**
   - Go to Deployments
   - Click "Redeploy" on the latest deployment

## ✅ Step 6: Final Testing

1. **Frontend-Backend Connection**
   - Visit your Vercel frontend
   - Check browser console for API calls
   - Verify no CORS errors

2. **API Functionality**
   - Test main features
   - Check if data loads from backend
   - Verify file uploads work (if applicable)

3. **Admin Panel**
   - Visit: `https://your-app-name.onrender.com/admin`
   - Login with admin credentials
   - Test admin functionality

## 🔧 Troubleshooting

### Common Issues & Solutions

#### 1. "php: command not found" Error ⚠️ **CRITICAL**
- **Problem**: PHP runtime not available
- **Solution**: 
  - **MUST select "PHP" as Runtime** in Render service creation
  - Ensure "Root Directory" is set to `backend`
  - Try alternative start command: `php -S 0.0.0.0:$PORT index.php`

#### 2. "Root Directory" Error
- **Problem**: Service fails to start
- **Solution**: Ensure Root Directory is set to `backend`, not repository root

#### 3. Database Connection Failed
- **Problem**: Health check shows database error
- **Solution**: 
  - Verify PostgreSQL credentials in environment variables
  - Check if database service is running
  - Ensure database name, user, and password are correct

#### 4. CORS Errors
- **Problem**: Frontend can't connect to backend
- **Solution**: 
  - Verify `CORS_ALLOWED_ORIGINS` includes your Vercel domain
  - Check `FRONTEND_URL` environment variable

#### 5. File Upload Issues
- **Problem**: Can't upload files
- **Solution**: 
  - Ensure `uploads/` directory exists and is writable
  - Check `STORAGE_TYPE` and `UPLOAD_PATH` settings

#### 6. Service Won't Start
- **Problem**: Deployment fails
- **Solution**: 
  - Check Start Command: `php -S 0.0.0.0:$PORT`
  - Verify PHP runtime is selected
  - Check deployment logs for specific errors

### Alternative Start Commands to Try:
```bash
# Option 1: Standard PHP server
php -S 0.0.0.0:$PORT

# Option 2: With specific entry point
php -S 0.0.0.0:$PORT index.php

# Option 3: With host binding
php -S 0.0.0.0:$PORT -t .

# Option 4: Alternative port binding
php -S 0.0.0.0:$PORT --host 0.0.0.0
```

### Debug Commands

```bash
# Check backend health
curl https://your-app-name.onrender.com/test_render.php

# Test API endpoint
curl https://your-app-name.onrender.com/api

# Check CORS headers
curl -H "Origin: https://diues.vercel.app" \
     -H "Access-Control-Request-Method: GET" \
     -H "Access-Control-Request-Headers: Content-Type" \
     -X OPTIONS \
     https://your-app-name.onrender.com/api
```

## 📱 Final URLs

After successful deployment:

- **Frontend**: https://diues.vercel.app
- **Backend**: https://your-app-name.onrender.com
- **API**: https://your-app-name.onrender.com/api
- **Admin**: https://your-app-name.onrender.com/admin
- **Health Check**: https://your-app-name.onrender.com/test_render.php

## 🎯 Success Indicators

- ✅ Backend responds to health check
- ✅ Database connection established
- ✅ API endpoints accessible
- ✅ Frontend can communicate with backend
- ✅ No CORS errors in browser console
- ✅ Admin panel functional
- ✅ File uploads working (if applicable)

## 📚 Additional Resources

- [Render Documentation](https://render.com/docs)
- [PHP on Render](https://render.com/docs/deploy-php)
- [PostgreSQL on Render](https://render.com/docs/databases)
- [CORS Configuration](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)

---

**Need Help?** Check the deployment logs in Render dashboard or refer to `DEPLOYMENT_CHECKLIST.md` for a step-by-step checklist.
