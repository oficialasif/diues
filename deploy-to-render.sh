#!/bin/bash

# DIU Esports Portal - Render Deployment Helper Script
# This script helps prepare your project for Render deployment

echo "🚀 DIU Esports Portal - Render Deployment Helper"
echo "================================================"

# Check if we're in the right directory
if [ ! -f "render.yaml" ]; then
    echo "❌ Error: render.yaml not found. Please run this script from the project root."
    exit 1
fi

echo "✅ Project structure verified"
echo ""

# Check backend directory
if [ ! -d "backend" ]; then
    echo "❌ Error: backend directory not found"
    exit 1
fi

echo "✅ Backend directory found"
echo ""

# Check if .env.production exists
if [ ! -f "backend/.env.production" ]; then
    echo "⚠️  Warning: .env.production not found"
    echo "   Creating from template..."
    cp backend/env.production.template backend/.env.production
    echo "   Please edit backend/.env.production with your actual values"
    echo ""
fi

# Check if logs directory exists
if [ ! -d "backend/logs" ]; then
    echo "📁 Creating logs directory..."
    mkdir -p backend/logs
    touch backend/logs/error.log
    chmod 755 backend/logs
    chmod 644 backend/logs/error.log
    echo "✅ Logs directory created"
    echo ""
fi

# Check if uploads directory exists
if [ ! -d "backend/uploads" ]; then
    echo "📁 Creating uploads directory..."
    mkdir -p backend/uploads/{highlights,icons,logos,photos,posters}
    chmod 755 backend/uploads
    chmod 755 backend/uploads/*
    echo "✅ Uploads directory created"
    echo ""
fi

echo "🎯 Deployment Checklist:"
echo "========================"
echo ""
echo "1. ✅ Create Render account at https://render.com"
echo "2. ✅ Connect your GitHub repository"
echo "3. ✅ Create PostgreSQL database service"
echo "4. ✅ Create Web Service with these settings:"
echo "   - Name: diu-esports-backend"
echo "   - Root Directory: backend"
echo "   - Runtime: PHP"
echo "   - Start Command: php -S 0.0.0.0:\$PORT"
echo ""
echo "5. ✅ Set environment variables in Render (see DEPLOYMENT_CHECKLIST.md)"
echo "6. ✅ Deploy and wait for completion"
echo "7. ✅ Test health check: https://your-app-name.onrender.com/test_render.php"
echo "8. ✅ Update Vercel environment variables"
echo ""
echo "📚 For detailed steps, see: DEPLOYMENT_CHECKLIST.md"
echo "🔧 For troubleshooting, see: RENDER_DEPLOYMENT.md"
echo ""
echo "🚀 Ready to deploy to Render!"
echo ""
echo "Next steps:"
echo "1. Go to https://render.com"
echo "2. Create new Web Service"
echo "3. Connect your repository"
echo "4. Set Root Directory to 'backend'"
echo "5. Configure environment variables"
echo "6. Deploy!"
