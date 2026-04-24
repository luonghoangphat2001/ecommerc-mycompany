import axiosClient from '../../../api/axiosClient';


const authService = {
  login: async (email, password) => {
    return await axiosClient.post('login', {
      email,
      password,
      device_name: 'storefront_web'
    });
  },

  register: async (userData) => {
    return await axiosClient.post('register', {
      ...userData,
      device_name: 'storefront_web'
    });
  },

  forgotPassword: async (email) => {
    return await axiosClient.post('forgot-password', { email });
  },

  resetPassword: async (data) => {
    return await axiosClient.post('reset-password', data);
  },

  logout: async () => {
    return await axiosClient.post('logout');
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

