import apiService from '../../../api/apiService';


const authService = {
  login: async (email, password) => {
    return apiService.post('auth/login', {
      email,
      password,
      device_name: 'storefront_web'
    });
  },

  register: async (userData) => {
    return apiService.post('auth/register', {
      ...userData,
      device_name: 'storefront_web'
    });
  },

  forgotPassword: async (email) => {
    return apiService.post('auth/forgot-password', { email });
  },

  resetPassword: async (data) => {
    return apiService.post('auth/reset-password', data);
  },

  logout: async () => {
    return apiService.post('auth/logout');
  },

  fetchUser: async () => {
    return apiService.get('auth/profile');
  },

  getCurrentUser: () => {
    // This will now be handled by useAuthStore, 
    // but keeping it as a helper that reads from localStorage 
    // if needed by other non-hook logic
    try {
      const storage = JSON.parse(localStorage.getItem('auth-storage'));
      return storage?.state?.user || null;
    } catch (e) {
      return null;
    }
  }
};

export default authService;
