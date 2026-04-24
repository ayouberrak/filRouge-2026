import axios from 'axios';
import echo from './echo';

const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    withCredentials: false,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Add socket ID for broadcasting (toOthers)
    if (echo && echo.socketId()) {
        config.headers['X-Socket-ID'] = echo.socketId();
    }

    return config;
});

export default api;
