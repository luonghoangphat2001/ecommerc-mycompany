import axios from 'axios';

import toast from 'react-hot-toast';

const apiBaseUrl = (process.env.REACT_APP_API_URL || '/api/v1').replace(/\/$/, '');

const axiosClient = axios.create({
  baseURL: apiBaseUrl,
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

let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
  failedQueue.forEach(prom => {
    if (error) {
      prom.reject(error);
    } else {
      prom.resolve(token);
    }
  });
  failedQueue = [];
};

axiosClient.interceptors.response.use(
  (response) => {
    return response.data;
  },
  async (error) => {
    const originalRequest = error.config;

    if (error.response) {
      const { status, data } = error.response;

      // Handle 401 Unauthorized
      if (status === 401 && !originalRequest._retry) {
        if (isRefreshing) {
          return new Promise(function (resolve, reject) {
            failedQueue.push({ resolve, reject });
          })
            .then(token => {
              originalRequest.headers['Authorization'] = 'Bearer ' + token;
              return axiosClient(originalRequest);
            })
            .catch(err => {
              return Promise.reject(err);
            });
        }

        originalRequest._retry = true;
        isRefreshing = true;

        return new Promise(function (resolve, reject) {
          axiosClient.post('auth/refresh-token')
            .then((refreshResponse) => {
              const accessToken = refreshResponse?.accessToken || refreshResponse?.token;
              try {
                const authStorage = JSON.parse(localStorage.getItem('auth-storage'));
                if (authStorage && authStorage.state) {
                  authStorage.state.accessToken = accessToken;
                  localStorage.setItem('auth-storage', JSON.stringify(authStorage));
                }
              } catch (e) {
                console.error('Failed to update token in storage', e);
              }

              axiosClient.defaults.headers.common['Authorization'] = 'Bearer ' + accessToken;
              originalRequest.headers['Authorization'] = 'Bearer ' + accessToken;
              processQueue(null, accessToken);
              resolve(axiosClient(originalRequest));
            })
            .catch((err) => {
              processQueue(err, null);
              localStorage.removeItem('auth-storage');
              window.location.href = '/login';
              reject(err);
            })
            .finally(() => {
              isRefreshing = false;
            });
        });
      }

      // Handle other common errors globally
      if (status === 403) {
        toast.error('You do not have permission to access this resource.', { id: 'error-403' });
      } else if (status === 404) {
        // Do not show toast for 404s by default, especially for menus, as they are handled by fallbacks
        // toast.error('The requested resource was not found.', { id: 'error-404' });
      } else if (status === 422) {
        const message = data?.message || 'Validation failed. Please check your inputs.';
        toast.error(message, { id: 'error-422' });
      } else if (status >= 500) {
        toast.error('A server error occurred. Please try again later.', { id: 'error-500' });
      }
    } else {
      // Network error or no response
      toast.error('Network error. Please check your connection.', { id: 'error-network' });
    }

    return Promise.reject(error);
  }
);

export default axiosClient;
