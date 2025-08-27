#!/bin/bash

# DIU Esports Community Portal - Setup Script
# This script helps set up the project for development and production

echo "🎮 DIU Esports Community Portal - Setup Script"
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root"
   exit 1
fi

# Check prerequisites
print_status "Checking prerequisites..."

# Check Node.js
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed. Please install Node.js 18+ first."
    exit 1
fi

NODE_VERSION=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
if [ "$NODE_VERSION" -lt 18 ]; then
    print_error "Node.js version 18+ is required. Current version: $(node -v)"
    exit 1
fi

print_success "Node.js $(node -v) is installed"

# Check npm
if ! command -v npm &> /dev/null; then
    print_error "npm is not installed"
    exit 1
fi

print_success "npm $(npm -v) is installed"

# Check PHP
if ! command -v php &> /dev/null; then
    print_warning "PHP is not installed. Backend functionality will not work."
    PHP_AVAILABLE=false
else
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1)
    if [ "$PHP_VERSION" -lt 8 ]; then
        print_warning "PHP version 8+ is recommended. Current version: $(php -v | head -n1)"
    fi
    print_success "PHP $(php -v | head -n1) is installed"
    PHP_AVAILABLE=true
fi

# Check MySQL
if ! command -v mysql &> /dev/null; then
    print_warning "MySQL is not installed. Backend functionality will not work."
    MYSQL_AVAILABLE=false
else
    print_success "MySQL is installed"
    MYSQL_AVAILABLE=true
fi

echo ""

# Frontend setup
print_status "Setting up frontend..."

if [ ! -d "node_modules" ]; then
    print_status "Installing Node.js dependencies..."
    npm install
    if [ $? -eq 0 ]; then
        print_success "Frontend dependencies installed"
    else
        print_error "Failed to install frontend dependencies"
        exit 1
    fi
else
    print_success "Frontend dependencies already installed"
fi

# Create environment file
if [ ! -f ".env.local" ]; then
    print_status "Creating environment file..."
    if [ -f "env.example" ]; then
        cp env.example .env.local
        print_success "Environment file created from template"
        print_warning "Please edit .env.local with your actual configuration"
    else
        print_warning "env.example not found, creating basic .env.local"
        cat > .env.local << EOF
# DIU Esports Community Portal - Environment Variables
NEXT_PUBLIC_API_BASE_URL=http://localhost/backend/api
NEXT_PUBLIC_SITE_URL=http://localhost:3000
EOF
        print_success "Basic environment file created"
    fi
else
    print_success "Environment file already exists"
fi

echo ""

# Backend setup
if [ "$PHP_AVAILABLE" = true ]; then
    print_status "Setting up backend..."
    
    # Create configuration files from templates
    if [ -f "backend/config/database.example.php" ] && [ ! -f "backend/config/database.php" ]; then
        print_status "Creating database configuration from template..."
        cp backend/config/database.example.php backend/config/database.php
        print_warning "Please edit backend/config/database.php with your database credentials"
    fi
    
    if [ -f "backend/config/auth.example.php" ] && [ ! -f "backend/config/auth.php" ]; then
        print_status "Creating auth configuration from template..."
        cp backend/config/auth.example.php backend/config/auth.php
        print_warning "Please edit backend/config/auth.php with your authentication settings"
    fi
    
    # Set upload directory permissions
    if [ -d "backend/uploads" ]; then
        print_status "Setting upload directory permissions..."
        chmod -R 755 backend/uploads/
        print_success "Upload directory permissions set"
    fi
    
    print_success "Backend setup completed"
else
    print_warning "Skipping backend setup (PHP not available)"
fi

echo ""

# Database setup
if [ "$MYSQL_AVAILABLE" = true ] && [ "$PHP_AVAILABLE" = true ]; then
    print_status "Database setup..."
    
    if [ -f "backend/config/schema.sql" ]; then
        print_warning "Database schema file found at backend/config/schema.sql"
        print_warning "Please import this file into your MySQL database manually:"
        echo "   mysql -u your_username -p your_database_name < backend/config/schema.sql"
    fi
    
    print_success "Database setup instructions provided"
else
    print_warning "Skipping database setup (MySQL or PHP not available)"
fi

echo ""

# Build frontend
print_status "Building frontend..."
npm run build
if [ $? -eq 0 ]; then
    print_success "Frontend built successfully"
else
    print_error "Frontend build failed"
    exit 1
fi

echo ""

# Final instructions
print_success "Setup completed successfully!"
echo ""
echo "🚀 Next steps:"
echo "1. Edit .env.local with your configuration"
if [ "$PHP_AVAILABLE" = true ]; then
    echo "2. Edit backend/config/database.php with your database credentials"
    echo "3. Edit backend/config/auth.php with your authentication settings"
    echo "4. Import database schema: mysql -u username -p database < backend/config/schema.sql"
fi
echo "5. Start development server: npm run dev"
echo "6. Access admin panel: http://localhost:3000/backend/admin"
echo ""
echo "📚 For more information, see:"
echo "   - README.md - Project overview and features"
echo "   - DEPLOYMENT.md - Deployment instructions"
echo "   - .gitignore - Files excluded from version control"
echo ""
echo "🔐 Admin credentials:"
echo "   Configure during setup - never commit to version control"
echo ""
echo "Happy coding! 🎮✨"
