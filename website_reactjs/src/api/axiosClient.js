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
    if (response && response.data) {
      return response.data;
    }
    return response;
  },
  async (error) => {
    const originalRequest = error.config;

    if (error.response?.status === 401 && !originalRequest._retry) {
      if (isRefreshing) {
        return new Promise(function(resolve, reject) {
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

      return new Promise(function(resolve, reject) {
        axiosClient.post('refresh-token')
          .then(({ accessToken }) => {
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

    return Promise.reject(error);
  }
);

export default axiosClient;

