import axios from 'axios';

const api = axios.create({
  baseURL: 'http://172.18.0.4/api', // process.env.APP_API_URL,
  withCredentials: true, // If you're using cookies for authentication
});

// Add a request interceptor to include auth token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('authToken');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, error => Promise.reject(error));

export default api;
