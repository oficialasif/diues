'use client';

import { useState, useEffect } from 'react';
import { apiService } from '@/services/api';

export default function ApiTestPage() {
  const [events, setEvents] = useState<any[]>([]);
  const [committee, setCommittee] = useState<any[]>([]);
  const [tournaments, setTournaments] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [rawResponse, setRawResponse] = useState<any>(null);

  useEffect(() => {
    const testApi = async () => {
      try {
        setLoading(true);
        setError(null);

        // Test direct fetch first
        console.log('Testing direct fetch...');
        const directResponse = await fetch('https://diu-esports-backend.onrender.com/events.php');
        const directData = await directResponse.json();
        console.log('Direct fetch result:', directData);
        setRawResponse(directData);

        // Test API service
        console.log('Testing API service...');
        const eventsData = await apiService.getEvents();
        console.log('API service events result:', eventsData);
        setEvents(eventsData);

        const committeeData = await apiService.getCommitteeMembers();
        console.log('API service committee result:', committeeData);
        setCommittee(committeeData);

        const tournamentsData = await apiService.getTournaments();
        console.log('API service tournaments result:', tournamentsData);
        setTournaments(tournamentsData);

      } catch (err) {
        console.error('API test error:', err);
        setError(err instanceof Error ? err.message : 'Unknown error');
      } finally {
        setLoading(false);
      }
    };

    testApi();
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-900 text-white p-8">
        <h1 className="text-3xl font-bold mb-8">API Test Page</h1>
        <div className="text-xl">Loading...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-900 text-white p-8">
      <h1 className="text-3xl font-bold mb-8">API Test Page</h1>
      
      {error && (
        <div className="bg-red-600 p-4 rounded mb-6">
          <h2 className="text-xl font-bold mb-2">Error:</h2>
          <p>{error}</p>
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Raw Response */}
        <div className="bg-gray-800 p-4 rounded">
          <h2 className="text-xl font-bold mb-4">Raw API Response</h2>
          <pre className="text-sm overflow-auto max-h-64">
            {JSON.stringify(rawResponse, null, 2)}
          </pre>
        </div>

        {/* Events */}
        <div className="bg-gray-800 p-4 rounded">
          <h2 className="text-xl font-bold mb-4">Events ({events.length})</h2>
          <pre className="text-sm overflow-auto max-h-64">
            {JSON.stringify(events, null, 2)}
          </pre>
        </div>

        {/* Committee */}
        <div className="bg-gray-800 p-4 rounded">
          <h2 className="text-xl font-bold mb-4">Committee ({committee.length})</h2>
          <pre className="text-sm overflow-auto max-h-64">
            {JSON.stringify(committee, null, 2)}
          </pre>
        </div>

        {/* Tournaments */}
        <div className="bg-gray-800 p-4 rounded">
          <h2 className="text-xl font-bold mb-4">Tournaments ({tournaments.length})</h2>
          <pre className="text-sm overflow-auto max-h-64">
            {JSON.stringify(tournaments, null, 2)}
          </pre>
        </div>
      </div>

      <div className="mt-8">
        <h2 className="text-xl font-bold mb-4">Environment Info</h2>
        <div className="bg-gray-800 p-4 rounded">
          <p><strong>NODE_ENV:</strong> {process.env.NODE_ENV}</p>
          <p><strong>API_BASE_URL:</strong> {process.env.NEXT_PUBLIC_API_BASE_URL || 'Not set'}</p>
        </div>
      </div>
    </div>
  );
}
