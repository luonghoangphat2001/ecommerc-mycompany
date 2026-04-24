import axios from 'axios';

const axiosClient = axios.create({
  baseURL: '/api/v1/',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

axiosClient.interceptors.request.use(
  (config) => {
    try {
      const authStorage = JSON.parse(localStorage.getItem('auth-storage'));
      const token = authStorage?.state?.accessToken;
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (e) {
      console.error('Error reading auth token', e);
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

axiosClient.interceptors.response.use(
  (response) => {
    if (response && response.data) {
      return response.data;
    }
    return response;
  },
  (error) => {
    const statusCode = error.response?.status;

    if (statusCode === 401) {
      // Clear storage and redirect
      localStorage.removeItem('auth-storage');
      window.location.href = '/login';
    }
    else if (statusCode === 403) {
      console.error('Forbidden access');
    }
    else if (statusCode === 500) {
      console.error('Internal Server Error');
    }

    return Promise.reject(error);
  }
);

export default axiosClient;

