# 🔧 Render Environment Variables Configuration

## 📋 Complete Environment Variables for DIU Esports Backend

Use these exact values when setting up your Render Web Service environment variables.

### 🌐 Application Settings
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://diu-esports-backend.onrender.com
FRONTEND_URL=https://diues.vercel.app
```

### 🗄️ Database Configuration (PostgreSQL)
```env
DB_DRIVER=pgsql
DB_HOST=dpg-d2qcflre5dus73bt42b0-a.oregon-postgres.render.com
DB_NAME=diu_esports_db
DB_USERNAME=diu_esports_user
DB_PASSWORD=N9P2tK3xOtsOKnpZqrk1PmtTPO34eFrA
DB_PORT=5432
```

### 📁 File Storage Settings
```env
STORAGE_TYPE=local
UPLOAD_PATH=uploads/
MAX_FILE_SIZE=5242880
ALLOWED_FILE_TYPES=image/jpeg,image/png,image/gif,image/webp
```

### 🔒 CORS & Security Settings
```env
CORS_ALLOWED_ORIGINS=https://diues.vercel.app,http://localhost:3000
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=Strict
```

### 📝 Logging Settings
```env
LOG_LEVEL=error
TIMEZONE=UTC
```

## 🚀 How to Set These in Render

### Step 1: Go to Your Web Service
1. Visit [render.com](https://render.com) and sign in
2. Click on your `diu-esports-backend` service
3. Go to **Environment** tab

### Step 2: Add Each Variable
Click **Add Environment Variable** and add each one:

| Key | Value |
|-----|-------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://diu-esports-backend.onrender.com` |
| `FRONTEND_URL` | `https://diues.vercel.app` |
| `DB_DRIVER` | `pgsql` |
| `DB_HOST` | `dpg-d2qcflre5dus73bt42b0-a.oregon-postgres.render.com` |
| `DB_NAME` | `diu_esports_db` |
| `DB_USERNAME` | `diu_esports_user` |
| `DB_PASSWORD` | `N9P2tK3xOtsOKnpZqrk1PmtTPO34eFrA` |
| `DB_PORT` | `5432` |
| `STORAGE_TYPE` | `local` |
| `UPLOAD_PATH` | `uploads/` |
| `MAX_FILE_SIZE` | `5242880` |
| `ALLOWED_FILE_TYPES` | `image/jpeg,image/png,image/gif,image/webp` |
| `CORS_ALLOWED_ORIGINS` | `https://diues.vercel.app,http://localhost:3000` |
| `SESSION_SECURE` | `true` |
| `SESSION_HTTP_ONLY` | `true` |
| `SESSION_SAME_SITE` | `Strict` |
| `LOG_LEVEL` | `error` |
| `TIMEZONE` | `UTC` |

### Step 3: Save and Redeploy
1. Click **Save Changes**
2. Your service will automatically redeploy
3. Wait for deployment to complete

## 🔍 Database Connection Details

Your PostgreSQL database is already set up with these credentials:

- **Hostname**: `dpg-d2qcflre5dus73bt42b0-a.oregon-postgres.render.com`
- **Port**: `5432`
- **Database**: `diu_esports_db`
- **Username**: `diu_esports_user`
- **Password**: `N9P2tK3xOtsOKnpZqrk1PmtTPO34eFrA`

## ✅ Verification Steps

After setting the environment variables:

1. **Check Deployment Logs**
   - Go to your service's **Logs** tab
   - Look for any environment variable errors

2. **Test Health Check**
   - Visit: `https://your-app-name.onrender.com/test_render.php`
   - Should show successful database connection

3. **Test API**
   - Visit: `https://your-app-name.onrender.com/api`
   - Should show API endpoints list

## 🚨 Important Notes

- **Never commit passwords to Git** - These are only for Render environment variables
- **Case sensitive** - Ensure exact spelling and case
- **No spaces** - Don't add spaces around the `=` sign
- **Quotes not needed** - Don't wrap values in quotes unless they contain spaces

## 🔧 Troubleshooting

If you encounter issues:

1. **Check Environment Variables**
   - Verify all variables are set correctly
   - Check for typos in keys or values

2. **Database Connection Issues**
   - Verify database service is running
   - Check if credentials match exactly

3. **Service Won't Start**
   - Check deployment logs for specific errors
   - Verify all required variables are set

---

**🎯 Your backend is now ready to deploy with these exact environment variables!**
