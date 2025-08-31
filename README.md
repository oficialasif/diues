# 🎮 DIU Esports Community Portal

A modern, responsive web application for the DIU Esports Community featuring a Next.js frontend and PHP backend with comprehensive admin panel functionality.

## ✨ Features

### Frontend (Next.js)
- **🎯 Hero Section**: Dynamic counters with real-time database updates
- **🏆 Tournaments & Events**: Comprehensive event management system
- **👥 Leadership Committee**: Team member showcase with roles and achievements
- **🖼️ Gallery**: Media showcase with categories and filtering
- **🤝 Sponsors & Partners**: Partnership management and display
- **🏅 Achievements**: Milestone tracking and highlights
- **📱 Responsive Design**: Mobile-first approach with modern UI/UX
- **🎨 Animations**: GSAP and Framer Motion for smooth interactions
- **🌐 SEO Optimized**: Meta tags and structured data

### Backend (PHP)
- **🔐 Admin Panel**: Secure authentication and role-based access
- **📊 Dashboard**: Comprehensive statistics and overview
- **🎮 Tournament Management**: Create, edit, and manage tournaments
- **📅 Event Management**: Schedule and organize community events
- **👥 Committee Management**: Manage team members and roles
- **🖼️ Media Management**: Upload and organize gallery content
- **🤝 Sponsor Management**: Partner and sponsor information
- **⚙️ Settings**: Site configuration and customization
- **📈 Analytics**: Registration tracking and statistics

### Database
- **🗄️ MySQL Database**: Robust data storage and management
- **🔗 Relational Design**: Optimized table structure for performance
- **📊 Sample Data**: Pre-populated with example content
- **🔄 Real-time Updates**: Live data synchronization

## 🚀 Getting Started

### Prerequisites
- **XAMPP/WAMP**: For local development environment
- **PHP 8.0+**: Backend runtime
- **MySQL 5.7+**: Database server
- **Node.js 18+**: Frontend development
- **Git**: Version control

### Installation

1. **Clone the Repository**
   ```bash
   git clone <your-repository-url>
   cd diuecport
   ```

2. **Backend Setup**
   ```bash
   cd backend
   # Configure database connection in config/database.php
   # Run setup scripts if needed
   ```

3. **Frontend Setup**
   ```bash
   npm install
   cp env.example .env.local
   # Edit .env.local with your configuration
   npm run dev
   ```

4. **Database Setup**
   - Create MySQL database
   - Import schema from `backend/config/schema.sql`
   - Run sample data scripts if needed

## 🏗️ Project Structure

```
diuecport/
├── app/                    # Next.js app directory
├── components/            # React components
│   ├── HeroSection.tsx   # Landing page hero
│   ├── GamesPortfolio.tsx # Tournaments showcase
│   ├── EventsNews.tsx    # Events display
│   ├── Leadership.tsx    # Committee showcase
│   ├── Gallery.tsx       # Media gallery
│   ├── Sponsors.tsx      # Partners display
│   └── ContactFooter.tsx # Contact information
├── services/             # API services
│   └── api.ts           # Backend communication
├── backend/              # PHP backend
│   ├── admin/           # Admin panel
│   ├── api/             # REST API endpoints
│   ├── config/          # Configuration files
│   └── uploads/         # File storage
├── public/               # Static assets
└── package.json          # Dependencies
```

## 🎯 Key Components

### GamesPortfolio
- Tournament display and filtering
- Registration system
- Game categorization
- Prize pool information

### EventsNews
- Event calendar and management
- Real-time countdown timers
- Event status tracking
- Location and timing details

### Leadership
- Committee member profiles
- Role and achievement display
- Social media integration
- Current and alumni members

### Gallery
- Media categorization
- Year-based filtering
- Image and video support
- Tag-based organization

## 🔧 Configuration

### Environment Variables
- `NEXT_PUBLIC_API_BASE_URL`: Backend API endpoint
- `NEXT_PUBLIC_SITE_URL`: Frontend application URL
- `NEXT_PUBLIC_APP_NAME`: Application name
- `NEXT_PUBLIC_APP_VERSION`: Version number

### Database Configuration
- Host and database name settings
- Connection parameters
- Table structure and relationships

## 📱 Responsive Design

- **Mobile First**: Optimized for mobile devices
- **Tablet Support**: Responsive breakpoints
- **Desktop Experience**: Enhanced desktop features
- **Touch Friendly**: Mobile-optimized interactions

## 🎨 UI/UX Features

- **Modern Design**: Clean and professional appearance
- **Dark Theme**: Eye-friendly color scheme
- **Smooth Animations**: GSAP and Framer Motion
- **Interactive Elements**: Hover effects and transitions
- **Loading States**: User feedback and progress indicators

## 🚀 Deployment

### Frontend (Vercel/Netlify)
- Build optimization
- Static asset optimization
- CDN distribution
- Environment variable configuration

### Backend (Shared Hosting/VPS)
- PHP compatibility
- Database setup
- File upload configuration
- Security hardening

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is developed for the DIU Esports Community. All rights reserved.

## 🆘 Support

For technical support or questions:
- Check the documentation
- Review the code comments
- Contact the development team

## 🔄 Updates

- Regular feature updates
- Security patches
- Performance improvements
- Bug fixes and maintenance

---

**Built for the DIU Esports Community**
