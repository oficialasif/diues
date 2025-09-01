// Test API URL and call
const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 
  (process.env.NODE_ENV === 'production' 
    ? 'https://diu-esports-backend.onrender.com/api'
    : 'http://localhost/diuecport/backend/api'
  );

console.log('Environment:', process.env.NODE_ENV);
console.log('API Base URL:', API_BASE_URL);
console.log('NEXT_PUBLIC_API_BASE_URL:', process.env.NEXT_PUBLIC_API_BASE_URL);

// Test API call
fetch(`${API_BASE_URL}/tournaments`)
  .then(response => response.json())
  .then(data => {
    console.log('API Response:', data);
  })
  .catch(error => {
    console.error('API Error:', error);
  });
