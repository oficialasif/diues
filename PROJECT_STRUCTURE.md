# DIU Esports Community Portal - Project Structure

## Overview
The project has been reorganized to separate frontend and backend components, making it easier to deploy on different platforms.

## New Project Structure

```
diuecport/
├── frontend/                 # Next.js Frontend Application
│   ├── app/                 # Next.js App Router
│   ├── components/          # React Components
│   ├── public/             # Static Assets
│   ├── services/           # API Services
│   ├── package.json        # Frontend Dependencies
│   ├── next.config.js      # Next.js Configuration
│   ├── tailwind.config.js  # Tailwind CSS Configuration
│   └── tsconfig.json       # TypeScript Configuration
├── backend/                # PHP Backend API
│   ├── api/               # API Endpoints
│   ├── admin/             # Admin Panel
│   ├── config/            # Configuration Files
│   ├── uploads/           # File Uploads
│   ├── index.php          # Backend Entry Point
│   └── composer.json      # PHP Dependencies
├── start-backend.sh       # Backend Server Script
├── render.yaml           # Render Deployment Config
└── README.md             # Project Documentation
```

## Development Setup

### Frontend Development
```bash
cd frontend
npm install
npm run dev
```
Frontend will be available at: http://localhost:3000 (or 3001 if 3000 is busy)

### Backend Development
```bash
# Option 1: Use the provided script
./start-backend.sh

# Option 2: Manual start
cd backend
php -S localhost:8080
```
Backend will be available at: http://localhost:8080

## Deployment

### Frontend (Vercel)
- Deploy the `frontend/` folder to Vercel
- Set environment variable: `NEXT_PUBLIC_API_BASE_URL=https://your-backend.onrender.com/api`

### Backend (Render)
- Deploy the `backend/` folder to Render
- Render will automatically detect this as a PHP project
- No Node.js files in the root directory

## Key Changes Made

1. **Separated Frontend and Backend**: Moved all Next.js files to `frontend/` folder
2. **Updated API Paths**: Changed API base URL to use port 8080 for local development
3. **Fixed Image Domains**: Updated Next.js config to allow images from backend server
4. **Created Backend Server Script**: Easy way to start PHP development server
5. **Cleaned Root Directory**: Removed Node.js files from root to help Render detect PHP

## Benefits

- ✅ Render can now detect this as a PHP project
- ✅ Frontend and backend can be deployed separately
- ✅ Cleaner project structure
- ✅ Easier development workflow
- ✅ Better separation of concerns

## API Endpoints

- **Health Check**: `GET /health`
- **API Base**: `GET /api`
- **Admin Panel**: `GET /admin`
- **Tournaments**: `GET /api/tournaments`
- **Events**: `GET /api/events`
- **Gallery**: `GET /api/gallery`
- **Committee**: `GET /api/committee`
- **Sponsors**: `GET /api/sponsors`
- **Achievements**: `GET /api/achievements`
- **Settings**: `GET /api/settings`
- **Stats**: `GET /api/stats`
- **Countdown**: `GET /api/countdown`

## File Uploads

Uploaded files are stored in `backend/uploads/` with the following structure:
- `uploads/posters/` - Tournament/Event posters
- `uploads/photos/` - Gallery photos
- `uploads/logos/` - Sponsor logos
- `uploads/icons/` - Achievement icons
- `uploads/highlights/` - Achievement highlights
