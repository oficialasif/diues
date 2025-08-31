'use client'

import { useState, useEffect } from 'react'
import { apiService } from '@/services/api'

export default function DebugPage() {
  const [apiUrl, setApiUrl] = useState('')
  const [tournaments, setTournaments] = useState([])
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    // Get the API URL being used
    const url = process.env.NEXT_PUBLIC_API_BASE_URL || 
      (process.env.NODE_ENV === 'production' 
        ? 'https://diu-esports-backend.onrender.com/api'
        : 'http://localhost/diuecport/backend/api'
      )
    setApiUrl(url)
  }, [])

  const testAPI = async () => {
    setLoading(true)
    setError('')
    try {
      const data = await apiService.getTournaments()
      setTournaments(data)
    } catch (err: any) {
      setError(err.message || 'Unknown error')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="container mx-auto p-8">
      <h1 className="text-3xl font-bold mb-6">API Debug Page</h1>
      
      <div className="bg-gray-100 p-4 rounded mb-6">
        <h2 className="text-xl font-semibold mb-2">Environment Info</h2>
        <p><strong>NODE_ENV:</strong> {process.env.NODE_ENV}</p>
        <p><strong>API URL:</strong> {apiUrl}</p>
        <p><strong>NEXT_PUBLIC_API_BASE_URL:</strong> {process.env.NEXT_PUBLIC_API_BASE_URL}</p>
      </div>

      <button 
        onClick={testAPI}
        disabled={loading}
        className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
      >
        {loading ? 'Testing...' : 'Test API'}
      </button>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4">
          <strong>Error:</strong> {error}
        </div>
      )}

      {tournaments.length > 0 && (
        <div className="mt-6">
          <h2 className="text-xl font-semibold mb-2">Tournaments ({tournaments.length})</h2>
          <div className="bg-green-100 p-4 rounded">
            <pre>{JSON.stringify(tournaments, null, 2)}</pre>
          </div>
        </div>
      )}
    </div>
  )
}
