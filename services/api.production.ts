// Production API Configuration for DIU Esports Community Portal
// Update your frontend environment variables to use this configuration

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'https://your-app-name.onrender.com/api';

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data: T;
  timestamp: string;
}

// ... (keep all your existing interfaces)

class ApiService {
  private async request<T>(endpoint: string, options: RequestInit = {}): Promise<ApiResponse<T>> {
    const url = `${API_BASE_URL}/${endpoint}`;
    
    const defaultOptions: RequestInit = {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      credentials: 'include', // Include cookies for authentication
      ...options,
    };

    try {
      const response = await fetch(url, defaultOptions);
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || `HTTP error! status: ${response.status}`);
      }
      
      return data;
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  // ... (keep all your existing methods)
}

export const apiService = new ApiService();
export default apiService;

// Environment Variables to set in Vercel:
// NEXT_PUBLIC_API_BASE_URL=https://your-app-name.onrender.com/api
// NEXT_PUBLIC_SITE_URL=https://your-vercel-app.vercel.app
// NEXT_PUBLIC_APP_NAME="DIU Esports Community"
// NEXT_PUBLIC_APP_VERSION="1.0.0"
// NEXT_PUBLIC_APP_DESCRIPTION="Modern esports community portal for Daffodil International University"
