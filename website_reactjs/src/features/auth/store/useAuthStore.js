import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import authService from '../services/authService';
import useCartStore from '../../cart/store/useCartStore';

const useAuthStore = create(
  persist(
    (set, get) => ({
      user: null,
      accessToken: null,
      isAuthenticated: false,
      isLoading: false,
      error: null,
      _hasHydrated: false,

      setHasHydrated: (state) => {
        set({ _hasHydrated: state });
      },

      login: async (email, password) => {
        set({ isLoading: true, error: null });
        try {
          const response = await authService.login(email, password);
          set({ 
            user: response.data.user, 
            accessToken: response.data.token,
            isAuthenticated: true, 
            isLoading: false 
          });

          // Trigger cart merge if user has a saved cart on backend (once API is ready)
          if (response.data.user.cart_items) {
            useCartStore.getState().mergeCart(response.data.user.cart_items);
          }

          return response;
        } catch (error) {
          set({ 
            error: error.response?.data?.message || 'Login failed', 
            isLoading: false 
          });
          throw error;
        }
      },

      logout: async () => {
        set({ isLoading: true });
        try {
          await authService.logout();
        } finally {
          set({ 
            user: null, 
            accessToken: null,
            isAuthenticated: false, 
            isLoading: false 
          });
        }
      },

      setUser: (user) => set({ user, isAuthenticated: !!user })
    }),
    {
      name: 'auth-storage',
      storage: createJSONStorage(() => localStorage),
      onRehydrateStorage: () => (state) => {
        state.setHasHydrated(true);
      },
      partialize: (state) => ({ 
        user: state.user, 
        accessToken: state.accessToken,
        isAuthenticated: state.isAuthenticated 
      }),
    }
  )
);

export default useAuthStore;
