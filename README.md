# 🎮 DIU Esports Community Portal

A modern, responsive web application for the DIU Esports Community featuring a Next.js frontend and PHP backend with comprehensive admin panel functionality.

## ✨ Features

- **🎯 Hero Section**: Dynamic counters with real-time database updates
- **🏆 Tournaments & Events**: Comprehensive event management system
- **👥 Committee Management**: Team member profiles and leadership structure
- **🖼️ Gallery System**: Photo and media management
- **🤝 Sponsors Portal**: Sponsor showcase and management
- **⚡ Real-time Countdown**: Admin-configurable event countdown timers
- **📱 Responsive Design**: Mobile-first approach with modern UI/UX
- **🔐 Admin Panel**: Full CRUD operations for all content types
- **🔄 Live Updates**: Real-time data synchronization

## 🚀 Tech Stack

### Frontend
- **Next.js 14** - React framework with App Router
- **TypeScript** - Type-safe development
- **Tailwind CSS** - Utility-first CSS framework
- **Framer Motion** - Smooth animations and transitions
- **Lucide React** - Beautiful icons

### Backend
- **PHP 8+** - Server-side logic
- **MySQL** - Database management
- **PDO** - Secure database operations
- **RESTful API** - Clean API architecture

## 📋 Prerequisites

- **Node.js 18+** and npm
- **PHP 8.0+** with PDO MySQL extension
- **MySQL 8.0+** or MariaDB 10.5+
- **Web Server** (Apache/Nginx) or XAMPP/WAMP

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/diuecport.git
cd diuecport
```

### 2. Frontend Setup
```bash
# Install dependencies
npm install

# Create environment file
cp .env.example .env.local

# Start development server
npm run dev
```

### 3. Backend Setup
```bash
# Navigate to backend directory
cd backend

# Copy configuration templates
cp config/database.example.php config/database.php
cp config/auth.example.php config/auth.php

# Edit database.php with your credentials
# Edit auth.php with your authentication settings
```

### 4. Database Setup
```bash
# Import the database schema
mysql -u your_username -p your_database_name < config/schema.sql

# Or use phpMyAdmin to import schema.sql
```

### 5. Web Server Configuration
- Point your web server to the project directory
- Ensure PHP has write permissions for uploads
- Configure URL rewriting for clean URLs

## ⚙️ Configuration

### Database Configuration
Edit `backend/config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'your_database_name';
private $username = 'your_username';
private $password = 'your_password';
```

### Environment Variables
Create `.env.local` in the root directory:
```env
NEXT_PUBLIC_API_BASE_URL=http://localhost/backend/api
NEXT_PUBLIC_SITE_URL=http://localhost:3000
```

## 🔐 Admin Access

### Default Admin Account
- **Email**: `asifmahmud053@gmail.com`
- **Password**: `admin*diuEsports`

### Admin Panel Features
- **Dashboard**: Overview and quick actions
- **Tournaments**: Event creation and management
- **Committee**: Team member management
- **Gallery**: Media upload and organization
- **Sponsors**: Partner showcase management
- **Countdown**: Event timer configuration
- **Settings**: System configuration

## 📁 Project Structure

```
diuecport/
├── app/                    # Next.js app directory
├── components/            # React components
├── services/             # API service layer
├── public/               # Static assets
├── backend/              # PHP backend
│   ├── admin/           # Admin panel
│   ├── api/             # REST API endpoints
│   ├── config/          # Configuration files
│   └── uploads/         # User uploads
├── package.json          # Node.js dependencies
└── README.md            # This file
```

## 🚀 Deployment

### Frontend (Vercel/Netlify)
```bash
npm run build
npm run start
```

### Backend (Shared Hosting/VPS)
- Upload `backend/` directory to your server
- Configure database credentials
- Set up URL rewriting
- Ensure proper file permissions

## 🔒 Security Features

- **CSRF Protection**: Built-in token validation
- **SQL Injection Prevention**: PDO prepared statements
- **Session Management**: Secure session handling
- **File Upload Validation**: Secure file handling
- **Admin Authentication**: Role-based access control

## 📱 Responsive Design

- **Mobile First**: Optimized for mobile devices
- **Progressive Enhancement**: Works without JavaScript
- **Accessibility**: WCAG compliant design
- **Cross-browser**: Modern browser support

## 🎨 Customization

### Styling
- Modify `tailwind.config.js` for theme changes
- Edit `app/globals.css` for custom styles
- Update color schemes in CSS variables

### Content
- Admin panel for easy content management
- Modular component system
- Configurable sections and layouts

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Check PDO MySQL extension is enabled

2. **Upload Permission Error**
   - Set write permissions on `backend/uploads/` directory
   - Check PHP upload settings in `php.ini`

3. **API Endpoint Not Found**
   - Verify `.htaccess` file is present
   - Check web server URL rewriting configuration
   - Ensure API base URL is correct in frontend

### Debug Mode
Enable debug logging in PHP files for detailed error information.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **DIU Esports Community** for inspiration and requirements
- **Next.js Team** for the amazing framework
- **Tailwind CSS** for the utility-first approach
- **Open Source Community** for various libraries and tools

## 📞 Support

For support and questions:
- **Email**: [your-email@example.com]
- **Issues**: [GitHub Issues](https://github.com/yourusername/diuecport/issues)
- **Documentation**: [Wiki](https://github.com/yourusername/diuecport/wiki)

---

**Made with ❤️ for the DIU Esports Community**
