# 🚀 Render Deployment Guide for DIU Esports Community Portal

This guide will walk you through deploying your DIU Esports Community Portal backend to Render.

## 📋 Prerequisites

- ✅ GitHub repository with your code
- ✅ Vercel frontend already deployed
- ✅ Render account (free tier available)
- ✅ Basic understanding of environment variables

## 🗄️ Step 1: Create PostgreSQL Database on Render

1. **Login to Render Dashboard**
   - Go to [render.com](https://render.com)
   - Sign in to your account

2. **Create New Database**
   - Click "New +" button
   - Select "PostgreSQL"
   - Choose "Free" plan
   - Set database name: `diu-esports-db`
   - Set user: `diu_esports_user`
   - Choose region closest to your users
   - Click "Create Database"

3. **Note Database Details**
   - Save the connection details (host, database name, username, password, port)
   - These will be used in environment variables

## 🌐 Step 2: Deploy Backend Web Service

1. **Create New Web Service**
   - Click "New +" button
   - Select "Web Service"
   - Connect your GitHub repository
   - Choose the repository: `diuesports`

2. **Configure Service Settings**
   - **Name**: `diu-esports-backend`
   - **Environment**: `PHP`
   - **Build Command**: Leave empty (not needed for PHP)
   - **Start Command**: Leave empty (Render handles PHP automatically)
   - **Plan**: Free

3. **Set Environment Variables**
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-app-name.onrender.com
   FRONTEND_URL=https://your-vercel-app.vercel.app
   DB_DRIVER=pgsql
   DB_HOST=your-postgres-host.render.com
   DB_NAME=diu_esports
   DB_USERNAME=diu_esports_user
   DB_PASSWORD=your-database-password
   DB_PORT=5432
   STORAGE_TYPE=local
   UPLOAD_PATH=uploads/
   ALLOWED_FILE_TYPES=image/jpeg,image/png,image/gif,image/webp
   MAX_FILE_SIZE=5242880
   ```

4. **Advanced Settings**
   - **Health Check Path**: `/api/stats`
   - **Auto-Deploy**: Enabled
   - **Branch**: `master`

5. **Create Service**
   - Click "Create Web Service"
   - Wait for deployment to complete

## 🔧 Step 3: Update Frontend Configuration

1. **Update Frontend Environment Variables**
   - Go to your Vercel dashboard
   - Update `NEXT_PUBLIC_API_BASE_URL` to your Render backend URL
   - Example: `https://diu-esports-backend.onrender.com/api`

2. **Redeploy Frontend**
   - Trigger a new deployment in Vercel
   - This ensures the frontend connects to the new backend

## 🗄️ Step 4: Initialize Database

1. **Access Your Render Backend**
   - Go to your deployed service URL
   - Add `/install.php` to the URL
   - Example: `https://diu-esports-backend.onrender.com/install.php`

2. **Run Database Setup**
   - The install script will create all necessary tables
   - Insert sample data
   - Create admin user

3. **Verify Installation**
   - Check if all tables are created
   - Verify admin user exists
   - Test API endpoints

## 📁 Step 5: Configure File Storage

### Option A: Local Storage (Default)
- Files are stored in Render's ephemeral storage
- **Note**: Files will be lost when the service restarts
- Good for testing and development

### Option B: Cloud Storage (Recommended for Production)

#### AWS S3 Setup
1. Create AWS S3 bucket
2. Get access keys
3. Set environment variables:
   ```
   STORAGE_TYPE=aws
   AWS_ACCESS_KEY_ID=your-key
   AWS_SECRET_ACCESS_KEY=your-secret
   AWS_DEFAULT_REGION=us-east-1
   AWS_S3_BUCKET=your-bucket-name
   ```

#### Cloudinary Setup
1. Create Cloudinary account
2. Get API credentials
3. Set environment variables:
   ```
   STORAGE_TYPE=cloudinary
   CLOUDINARY_CLOUD_NAME=your-cloud-name
   CLOUDINARY_API_KEY=your-api-key
   CLOUDINARY_API_SECRET=your-api-secret
   ```

## 🔒 Step 6: Security Configuration

1. **Update Admin Credentials**
   - Change default admin password
   - Use strong, unique passwords
   - Enable two-factor authentication if possible

2. **CORS Configuration**
   - Ensure only your Vercel domain is allowed
   - Remove localhost from production CORS

3. **Environment Variables**
   - Never commit sensitive data to Git
   - Use Render's environment variable system
   - Rotate passwords regularly

## 🧪 Step 7: Testing

1. **Test API Endpoints**
   - Health check: `/api/stats`
   - Tournaments: `/api/tournaments`
   - Events: `/api/events`
   - Admin login: `/admin/login.php`

2. **Test File Uploads**
   - Upload images through admin panel
   - Verify files are accessible
   - Test file deletion

3. **Test Frontend Integration**
   - Ensure frontend can fetch data
   - Test all components
   - Verify responsive design

## 📊 Step 8: Monitoring

1. **Render Dashboard**
   - Monitor service health
   - Check logs for errors
   - Monitor resource usage

2. **Database Monitoring**
   - Check database performance
   - Monitor connection count
   - Review slow queries

3. **Error Logging**
   - Check error logs regularly
   - Set up alerts for critical errors
   - Monitor API response times

## 🚨 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Verify environment variables
   - Check database status in Render
   - Ensure database is accessible

2. **File Upload Errors**
   - Check upload directory permissions
   - Verify file size limits
   - Check storage configuration

3. **CORS Errors**
   - Verify frontend URL in CORS settings
   - Check environment variables
   - Clear browser cache

4. **Service Won't Start**
   - Check build logs
   - Verify PHP version compatibility
   - Check for syntax errors

### Debug Mode

To enable debug mode temporarily:
```
APP_DEBUG=true
```

**Remember to disable debug mode in production!**

## 🔄 Updates and Maintenance

1. **Automatic Deployments**
   - Render automatically deploys on Git push
   - Monitor deployment status
   - Rollback if needed

2. **Database Backups**
   - Render provides automatic backups
   - Download backups regularly
   - Test restore procedures

3. **Security Updates**
   - Keep dependencies updated
   - Monitor security advisories
   - Apply patches promptly

## 📞 Support

- **Render Documentation**: [docs.render.com](https://docs.render.com)
- **Render Support**: Available in dashboard
- **Community Forums**: Stack Overflow, Reddit

## 🎯 Next Steps

After successful deployment:

1. **Performance Optimization**
   - Implement caching
   - Optimize database queries
   - Use CDN for static assets

2. **Monitoring Setup**
   - Set up uptime monitoring
   - Configure error alerts
   - Performance tracking

3. **Backup Strategy**
   - Regular database backups
   - File storage backups
   - Disaster recovery plan

---

**🎉 Congratulations! Your DIU Esports Community Portal is now deployed on Render!**

The backend is now accessible via your Render URL, and your Vercel frontend can communicate with it seamlessly.
