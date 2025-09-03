'use client';

import { useState, useEffect } from 'react';

interface ApiTestResult {
  endpoint: string;
  status: 'loading' | 'success' | 'error';
  data?: any;
  error?: string;
  count?: number;
}

export default function DebugApiPage() {
  const [results, setResults] = useState<ApiTestResult[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [apiBaseUrl, setApiBaseUrl] = useState('');

  useEffect(() => {
    // Get the API base URL from environment or construct it
    const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL || 'https://diu-esports-backend.onrender.com/api';
    setApiBaseUrl(baseUrl);
  }, []);

  const testEndpoint = async (endpoint: string, name: string) => {
    try {
      const response = await fetch(`${apiBaseUrl}/${endpoint}`);
      const data = await response.json();
      
      if (response.ok && data.success) {
        return {
          endpoint: name,
          status: 'success' as const,
          data: data.data,
          count: Array.isArray(data.data) ? data.data.length : 1
        };
      } else {
        return {
          endpoint: name,
          status: 'error' as const,
          error: data.message || `HTTP ${response.status}`
        };
      }
    } catch (error: any) {
      return {
        endpoint: name,
        status: 'error' as const,
        error: error.message || 'Network error'
      };
    }
  };

  const testAllEndpoints = async () => {
    setIsLoading(true);
    const newResults: ApiTestResult[] = [];

    const endpoints = [
      { url: 'events', name: 'Events' },
      { url: 'committee', name: 'Committee' },
      { url: 'tournaments', name: 'Tournaments' },
      { url: 'gallery', name: 'Gallery' },
      { url: 'sponsors', name: 'Sponsors' },
      { url: 'achievements', name: 'Achievements' },
      { url: 'settings', name: 'Settings' },
      { url: 'stats', name: 'Stats' }
    ];

    for (const endpoint of endpoints) {
      newResults.push({
        endpoint: endpoint.name,
        status: 'loading'
      });
      setResults([...newResults]);

      const result = await testEndpoint(endpoint.url, endpoint.name);
      newResults[newResults.length - 1] = result;
      setResults([...newResults]);
    }

    setIsLoading(false);
  };

  const testDirectBackend = async () => {
    try {
      const response = await fetch('https://diu-esports-backend.onrender.com/quick_api_test.php');
      const data = await response.json();
      console.log('Direct backend test:', data);
      alert('Direct backend test completed. Check console for results.');
    } catch (error) {
      console.error('Direct backend test failed:', error);
      alert('Direct backend test failed. Check console for errors.');
    }
  };

  return (
    <div className="min-h-screen bg-gray-900 text-white p-8">
      <div className="max-w-6xl mx-auto">
        <h1 className="text-3xl font-bold mb-8 text-center">
          🔍 DIU Esports - API Debug Tool
        </h1>

        <div className="bg-gray-800 rounded-lg p-6 mb-8">
          <h2 className="text-xl font-semibold mb-4">Environment Information</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <strong>API Base URL:</strong>
              <p className="text-green-400 font-mono break-all">
                {apiBaseUrl}
              </p>
            </div>
            <div>
              <strong>Node Environment:</strong>
              <p className="text-blue-400 font-mono">
                {process.env.NODE_ENV || 'Not set'}
              </p>
            </div>
            <div>
              <strong>Frontend URL:</strong>
              <p className="text-purple-400 font-mono">
                {typeof window !== 'undefined' ? window.location.origin : 'Server-side'}
              </p>
            </div>
            <div>
              <strong>Timestamp:</strong>
              <p className="text-yellow-400 font-mono">
                {new Date().toISOString()}
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
            onClick={testDirectBackend}
            className="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg font-semibold transition-colors"
          >
            Test Direct Backend
          </button>
        </div>

        {results.length > 0 && (
          <div className="bg-gray-800 rounded-lg p-6">
            <h2 className="text-xl font-semibold mb-4">API Test Results</h2>
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
          <h2 className="text-xl font-semibold mb-4">Quick Links</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h3 className="font-semibold mb-2">Backend Tests:</h3>
              <div className="space-y-2">
                <a 
                  href="https://diu-esports-backend.onrender.com/quick_api_test.php" 
                  target="_blank" 
                  className="block text-blue-400 hover:text-blue-300"
                >
                  Quick API Test
                </a>
                <a 
                  href="https://diu-esports-backend.onrender.com/test_api_endpoints.php" 
                  target="_blank" 
                  className="block text-blue-400 hover:text-blue-300"
                >
                  Full API Test
                </a>
                <a 
                  href="https://diu-esports-backend.onrender.com/admin" 
                  target="_blank" 
                  className="block text-blue-400 hover:text-blue-300"
                >
                  Admin Panel
                </a>
              </div>
            </div>
            <div>
              <h3 className="font-semibold mb-2">Frontend Tests:</h3>
              <div className="space-y-2">
                <a 
                  href="/test-api" 
                  className="block text-blue-400 hover:text-blue-300"
                >
                  Frontend API Test
                </a>
                <a 
                  href="/" 
                  className="block text-blue-400 hover:text-blue-300"
                >
                  Main Website
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
