'use client';

import { useState, useEffect } from 'react';
import apiService from '../../services/api';

interface TestResult {
  endpoint: string;
  status: 'loading' | 'success' | 'error';
  data?: any;
  error?: string;
  count?: number;
}

export default function TestApiPage() {
  const [results, setResults] = useState<TestResult[]>([]);
  const [isLoading, setIsLoading] = useState(false);

  const endpoints = [
    { name: 'Committee', method: () => apiService.getCommitteeMembers() },
    { name: 'Tournaments', method: () => apiService.getTournaments() },
    { name: 'Events', method: () => apiService.getEvents() },
    { name: 'Gallery', method: () => apiService.getGalleryItems() },
    { name: 'Sponsors', method: () => apiService.getSponsors() },
    { name: 'Achievements', method: () => apiService.getAchievements() },
    { name: 'Settings', method: () => apiService.getSiteSettings() },
    { name: 'Stats', method: () => apiService.getStats() },
  ];

  const testAllEndpoints = async () => {
    setIsLoading(true);
    const newResults: TestResult[] = [];

    for (const endpoint of endpoints) {
      newResults.push({
        endpoint: endpoint.name,
        status: 'loading'
      });
      setResults([...newResults]);

      try {
        const data = await endpoint.method();
        newResults[newResults.length - 1] = {
          endpoint: endpoint.name,
          status: 'success',
          data: data,
          count: Array.isArray(data) ? data.length : 1
        };
      } catch (error: any) {
        newResults[newResults.length - 1] = {
          endpoint: endpoint.name,
          status: 'error',
          error: error.message || 'Unknown error'
        };
      }
      setResults([...newResults]);
    }

    setIsLoading(false);
  };

  const testDirectApi = async () => {
    try {
      const response = await fetch('/api/committee');
      const data = await response.json();
      console.log('Direct API test result:', data);
      alert('Direct API test completed. Check console for results.');
    } catch (error) {
      console.error('Direct API test failed:', error);
      alert('Direct API test failed. Check console for errors.');
    }
  };

  useEffect(() => {
    // Test API base URL
    console.log('API Base URL:', process.env.NEXT_PUBLIC_API_BASE_URL);
    console.log('Node Environment:', process.env.NODE_ENV);
  }, []);

  return (
    <div className="min-h-screen bg-gray-900 text-white p-8">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold mb-8 text-center">
          🚀 DIU Esports - API Connection Test
        </h1>

        <div className="bg-gray-800 rounded-lg p-6 mb-8">
          <h2 className="text-xl font-semibold mb-4">Environment Information</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <strong>API Base URL:</strong>
              <p className="text-green-400 font-mono">
                {process.env.NEXT_PUBLIC_API_BASE_URL || 'Not set'}
              </p>
            </div>
            <div>
              <strong>Node Environment:</strong>
              <p className="text-blue-400 font-mono">
                {process.env.NODE_ENV || 'Not set'}
              </p>
            </div>
            <div>
              <strong>Site URL:</strong>
              <p className="text-purple-400 font-mono">
                {process.env.NEXT_PUBLIC_SITE_URL || 'Not set'}
              </p>
            </div>
            <div>
              <strong>App Name:</strong>
              <p className="text-yellow-400 font-mono">
                {process.env.NEXT_PUBLIC_APP_NAME || 'Not set'}
              </p>
            </div>
          </div>
        </div>

        <div className="flex gap-4 mb-8">
          <button
            onClick={testAllEndpoints}
            disabled={isLoading}
            className="bg-green-600 hover:bg-green-700 disabled:bg-gray-600 px-6 py-3 rounded-lg font-semibold transition-colors"
          >
            {isLoading ? 'Testing...' : 'Test All API Endpoints'}
          </button>
          
          <button
            onClick={testDirectApi}
            className="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg font-semibold transition-colors"
          >
            Test Direct API Call
          </button>
        </div>

        {results.length > 0 && (
          <div className="bg-gray-800 rounded-lg p-6">
            <h2 className="text-xl font-semibold mb-4">Test Results</h2>
            <div className="space-y-4">
              {results.map((result, index) => (
                <div
                  key={index}
                  className={`p-4 rounded-lg border-l-4 ${
                    result.status === 'success'
                      ? 'bg-green-900 border-green-500'
                      : result.status === 'error'
                      ? 'bg-red-900 border-red-500'
                      : 'bg-yellow-900 border-yellow-500'
                  }`}
                >
                  <div className="flex items-center justify-between">
                    <h3 className="font-semibold">{result.endpoint}</h3>
                    <div className="flex items-center gap-2">
                      {result.status === 'loading' && (
                        <span className="text-yellow-400">⏳ Loading...</span>
                      )}
                      {result.status === 'success' && (
                        <span className="text-green-400">
                          ✅ Success ({result.count} items)
                        </span>
                      )}
                      {result.status === 'error' && (
                        <span className="text-red-400">❌ Error</span>
                      )}
                    </div>
                  </div>
                  
                  {result.error && (
                    <p className="text-red-300 mt-2 font-mono text-sm">
                      {result.error}
                    </p>
                  )}
                  
                  {result.data && result.status === 'success' && (
                    <details className="mt-2">
                      <summary className="cursor-pointer text-blue-400 hover:text-blue-300">
                        View Data ({Array.isArray(result.data) ? result.data.length : 1} items)
                      </summary>
                      <pre className="mt-2 p-3 bg-gray-900 rounded text-xs overflow-auto max-h-40">
                        {JSON.stringify(result.data, null, 2)}
                      </pre>
                    </details>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        <div className="mt-8 bg-gray-800 rounded-lg p-6">
          <h2 className="text-xl font-semibold mb-4">Troubleshooting Steps</h2>
          <div className="space-y-3 text-sm">
            <div className="flex items-start gap-3">
              <span className="text-blue-400 font-bold">1.</span>
              <div>
                <strong>Check Environment Variables:</strong>
                <p className="text-gray-300">
                  Make sure NEXT_PUBLIC_API_BASE_URL is set to: https://diu-esports-backend.onrender.com/api
                </p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <span className="text-blue-400 font-bold">2.</span>
              <div>
                <strong>Test Backend Directly:</strong>
                <p className="text-gray-300">
                  Visit: <a href="https://diu-esports-backend.onrender.com/test_api_endpoints.php" target="_blank" className="text-blue-400 hover:underline">Backend API Test</a>
                </p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <span className="text-blue-400 font-bold">3.</span>
              <div>
                <strong>Check Browser Console:</strong>
                <p className="text-gray-300">
                  Open Developer Tools (F12) and check for CORS or network errors
                </p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <span className="text-blue-400 font-bold">4.</span>
              <div>
                <strong>Verify Data in Admin Panel:</strong>
                <p className="text-gray-300">
                  Make sure you've added data in the admin panel first
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
