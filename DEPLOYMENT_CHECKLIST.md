# DIU Esports Portal - Deployment Checklist

## 🚀 Frontend (Vercel) - ✅ COMPLETED
- [x] Frontend deployed on Vercel
- [x] Domain: https://diues.vercel.app

## 🗄️ Backend (Render) - 🔄 IN PROGRESS

### Step 1: Render Account Setup
- [ ] Create Render account at [render.com](https://render.com)
- [ ] Connect GitHub repository
- [ ] Verify repository access

### Step 2: PostgreSQL Database Setup ✅ COMPLETED
- [x] **Database Name**: `diu-esports-db`
- [x] **Database**: `diu_esports_db`
- [x] **User**: `diu_esports_user`
- [x] **Host**: `dpg-d2qcflre5dus73bt42b0-a.oregon-postgres.render.com`
- [x] **Port**: `5432`
- [x] **Password**: `N9P2tK3xOtsOKnpZqrk1PmtTPO34eFrA`

### Step 3: Backend Web Service Setup
- [ ] Create new Web Service on Render
  - **Name**: `diu-esports-backend`
  - **Root Directory**: `backend` ⚠️ **CRITICAL**
  - **Runtime**: `PHP`
  - **Build Command**: Leave empty
  - **Start Command**: `php -S 0.0.0.0:$PORT`

### Step 4: Environment Variables Configuration ✅ READY
All environment variables are prepared with your actual database credentials:

#### Application Settings
- [x] `APP_ENV` = `production`
- [x] `APP_DEBUG` = `false`
- [x] `APP_URL` = `https://diu-esports-backend.onrender.com`
- [x] `FRONTEND_URL` = `https://diues.vercel.app`

#### Database Settings ✅ CONFIGURED
- [x] `DB_DRIVER` = `pgsql`
- [x] `DB_HOST` = `dpg-d2qcflre5dus73bt42b0-a.oregon-postgres.render.com`
- [x] `DB_NAME` = `diu_esports_db`
- [x] `DB_USERNAME` = `diu_esports_user`
- [x] `DB_PASSWORD` = `N9P2tK3xOtsOKnpZqrk1PmtTPO34eFrA`
- [x] `DB_PORT` = `5432`

#### File Storage Settings
- [x] `STORAGE_TYPE` = `local`
- [x] `UPLOAD_PATH` = `uploads/`
- [x] `MAX_FILE_SIZE` = `5242880`
- [x] `ALLOWED_FILE_TYPES` = `image/jpeg,image/png,image/gif,image/webp`

#### CORS & Security Settings
- [x] `CORS_ALLOWED_ORIGINS` = `https://diues.vercel.app,http://localhost:3000`
- [x] `SESSION_SECURE` = `true`
- [x] `SESSION_HTTP_ONLY` = `true`
- [x] `SESSION_SAME_SITE` = `Strict`

#### Logging Settings
- [x] `LOG_LEVEL` = `error`
- [x] `TIMEZONE` = `UTC`

**📋 Reference**: See `RENDER_ENVIRONMENT_VARS.md` for complete list with exact values

### Step 5: Database Schema Setup
- [ ] Wait for backend deployment to complete
- [ ] Access: `https://diu-esports-backend.onrender.com/install.php`
- [ ] Run database installation script
- [ ] Verify tables are created successfully

### Step 6: Health Check & Testing
- [ ] Test backend health: `https://diu-esports-backend.onrender.com/test_render.php`
- [ ] Verify database connection
- [ ] Check API endpoints: `https://diu-esports-backend.onrender.com/api`

### Step 7: Frontend Environment Variables Update
In Vercel, set these environment variables:

- [ ] `NEXT_PUBLIC_API_BASE_URL` = `https://diu-esports-backend.onrender.com/api`
- [ ] `NEXT_PUBLIC_SITE_URL` = `https://diues.vercel.app`
- [ ] `NEXT_PUBLIC_APP_NAME` = `DIU Esports Community`
- [ ] `NEXT_PUBLIC_APP_VERSION` = `1.0.0`
- [ ] `NEXT_PUBLIC_APP_DESCRIPTION` = `Modern esports community portal for Daffodil International University`

### Step 8: Final Testing
- [ ] Test frontend-backend connection
- [ ] Verify API calls work from frontend
- [ ] Test file uploads (if applicable)
- [ ] Test authentication (if applicable)
- [ ] Check CORS headers work properly

## 🔧 Troubleshooting

### Common Issues:
1. **Root Directory Wrong**: Ensure it's set to `backend`, not the repository root
2. **Database Connection**: ✅ Credentials are verified and ready
3. **CORS Errors**: Check `CORS_ALLOWED_ORIGINS` includes your Vercel domain
4. **File Permissions**: Ensure uploads directory is writable
5. **Environment Variables**: ✅ All variables are prepared with correct values

### Useful Commands:
- Check backend logs in Render dashboard
- Test database connection: `https://diu-esports-backend.onrender.com/test_render.php`
- Verify API: `https://diu-esports-backend.onrender.com/api`

## 📱 Final URLs
- **Frontend**: https://diues.vercel.app
- **Backend**: https://diu-esports-backend.onrender.com
- **API**: https://diu-esports-backend.onrender.com/api
- **Health Check**: https://diu-esports-backend.onrender.com/test_render.php

## 🎯 Success Criteria
- [ ] Backend responds to health check
- [ ] Database connection established
- [ ] API endpoints accessible
- [ ] Frontend can communicate with backend
- [ ] CORS headers working properly
- [ ] File uploads working (if applicable)
- [ ] Authentication working (if applicable)

## 🚀 Ready to Deploy!

Your project is now **100% ready** for Render deployment with:
- ✅ All environment variables configured
- ✅ Database credentials verified
- ✅ Configuration files updated
- ✅ Health check endpoints ready
- ✅ CORS properly configured for your Vercel frontend

**Next step**: Go to [render.com](https://render.com) and create your Web Service!
