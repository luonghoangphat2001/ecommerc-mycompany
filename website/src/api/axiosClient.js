import axios from 'axios';
import toast from 'react-hot-toast';
import useSettingsStore from '../store/useSettingsStore';
import viLocales from '../locales/vi.json';
import enLocales from '../locales/en.json';

const rawApiUrl = (process.env.REACT_APP_API_URL || '/api/v1/storefront').replace(/\/$/, '');
const apiBaseUrl = rawApiUrl.endsWith('/api/v1') ? `${rawApiUrl}/storefront` : rawApiUrl;

const getActiveLanguage = () => {
  try {
    const storeLang = useSettingsStore?.getState?.()?.language;
    if (storeLang === 'vi' || storeLang === 'en') {
      return storeLang;
    }
    const saved = localStorage.getItem('app_language');
    if (saved === 'vi' || saved === 'en') {
      return saved;
    }
  } catch (e) {
    // ignore
  }
  return 'vi';
};

const getLocalizedErrorMessage = (errorKey, fallback) => {
  try {
    const translated = useSettingsStore?.getState?.()?.translate?.(`errors.${errorKey}`);
    if (translated) return translated;
  } catch (e) {
    // ignore
  }
  const lang = getActiveLanguage();
  const dict = lang === 'en' ? enLocales : viLocales;
  return dict?.errors?.[errorKey] || fallback;
};

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
      const token = authStorage?.state?.access_token;
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (e) {
      console.error('Error reading auth token', e);
    }

    const lang = getActiveLanguage();
    config.headers['Accept-Language'] = lang;
    config.headers['X-Locale'] = lang;
    config.params = {
      locale: lang,
      ...(config.params || {}),
    };

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
  failedQueue.forEach((prom) => {
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
      if (status === 401 && originalRequest && !originalRequest._retry && !String(originalRequest.url).includes('auth/login')) {
        if (isRefreshing) {
          return new Promise(function (resolve, reject) {
            failedQueue.push({ resolve, reject });
          })
            .then((token) => {
              originalRequest.headers['Authorization'] = 'Bearer ' + token;
              return axiosClient(originalRequest);
            })
            .catch((err) => {
              return Promise.reject(err);
            });
        }

        originalRequest._retry = true;
        isRefreshing = true;

        return new Promise(function (resolve, reject) {
          axiosClient
            .post('auth/refresh-token')
            .then((refreshResponse) => {
              const newAccessToken =
                refreshResponse?.data?.access_token || refreshResponse?.access_token;
              try {
                const authStorage = JSON.parse(localStorage.getItem('auth-storage'));
                if (authStorage && authStorage.state && newAccessToken) {
                  authStorage.state.access_token = newAccessToken;
                  localStorage.setItem('auth-storage', JSON.stringify(authStorage));
                }
              } catch (e) {
                console.error('Failed to update token in storage', e);
              }

              axiosClient.defaults.headers.common['Authorization'] = 'Bearer ' + newAccessToken;
              originalRequest.headers['Authorization'] = 'Bearer ' + newAccessToken;
              processQueue(null, newAccessToken);
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

      // Handle other common errors globally with localized messages
      if (status === 403) {
        toast.error(
          getLocalizedErrorMessage('forbidden', 'You do not have permission to access this resource.'),
          { id: 'error-403' }
        );
      } else if (status === 404) {
        // Do not show toast for 404s by default, handled by fallbacks
      } else if (status === 422) {
        const message =
          data?.message ||
          getLocalizedErrorMessage('validation', 'Validation failed. Please check your inputs.');
        toast.error(message, { id: 'error-422' });
      } else if (status >= 500) {
        toast.error(
          getLocalizedErrorMessage('server', 'A server error occurred. Please try again later.'),
          { id: 'error-500' }
        );
      }
    } else {
      // Network error or no response
      toast.error(
        getLocalizedErrorMessage('network', 'Network error. Please check your connection.'),
        { id: 'error-network' }
      );
    }

    return Promise.reject(error);
  }
);

export default axiosClient;
