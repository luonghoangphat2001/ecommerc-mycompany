import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import authService from '../services/authService';
import useCartStore from '../../cart/store/useCartStore';
import { unwrapApiObject } from '../../../api/apiResponse';

const useAuthStore = create(
  persist(
    (set, get) => ({
      user: null,
      access_token: null,
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
          const data = unwrapApiObject(response);
          const user = data.user || data;
          const token = data.access_token;
          set({
            user,
            access_token: token || null,
            isAuthenticated: true,
            isLoading: false
          });

          // Trigger cart merge if user has a saved cart on backend (once API is ready)
          if (user?.cart_items) {
            useCartStore.getState().mergeCart(user.cart_items);
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
            access_token: null,
            isAuthenticated: false,
            isLoading: false
          });
        }
      },

      setUser: (user) => set({ user, isAuthenticated: !!user }),

      refreshUser: async () => {
        try {
          const response = await authService.fetchUser();
          const data = unwrapApiObject(response);
          set({ user: data.user || data });
          return data;
        } catch (error) {
          console.error('Failed to refresh user:', error);
          return null;
        }
      }
    }),
    {
      name: 'auth-storage',
      version: 1,
      storage: createJSONStorage(() => localStorage),
      migrate: (persistedState) => {
        const token = persistedState?.state?.access_token ?? persistedState?.state?.accessToken ?? null;

        return {
          ...persistedState,
          state: {
            ...persistedState?.state,
            access_token: token,
          },
        };
      },
      onRehydrateStorage: () => (state) => {
        state.setHasHydrated(true);
      },
      partialize: (state) => ({
        user: state.user,
        access_token: state.access_token,
        isAuthenticated: state.isAuthenticated
      }),
    }
  )
);

export default useAuthStore;
