#!/bin/bash

# Start PHP backend server
echo "Starting DIU Esports Backend Server..."
echo "Backend will be available at: http://localhost:8080"
echo "API endpoints: http://localhost:8080/api"
echo "Admin panel: http://localhost:8080/admin"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

cd backend
php -S localhost:8080
