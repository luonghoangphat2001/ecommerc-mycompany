import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import authService from '../services/authService';
import useCartStore from '../../cart/store/useCartStore';
import { unwrapApiObject } from '../../../api/apiResponse';
import tokenManager from '../services/tokenManager';

const useAuthStore = create(
  persist(
    (set, get) => ({
      user: null,
      access_token: null,
      refresh_token: null,
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
          const user = data.user || data.profile || null;
          const token = data.access_token || data.accessToken || data.token || null;
          const refreshToken = data.refresh_token || null;

          if (!token) {
            throw new Error('Login response did not include an access token');
          }

          set({
            user,
            access_token: token,
            refresh_token: refreshToken,
            isAuthenticated: true,
            isLoading: false
          });
          tokenManager.setAccessToken(token);

          if (user?.cart_items) {
            useCartStore.getState().mergeCart(user.cart_items);
          }

          return response;
        } catch (error) {
          set({
            error: error.response?.data?.message || 'Login failed',
            isLoading: false
          });
          tokenManager.clearAccessToken();
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
            refresh_token: null,
            isAuthenticated: false,
            isLoading: false
          });
        }
      },

      setUser: (user, token) => set((state) => {
        if (token !== undefined) tokenManager.setAccessToken(token);
        return {
          user,
          access_token: token !== undefined ? token : state.access_token,
          isAuthenticated: !!user,
        };
      }),

      refreshUser: async () => {
        try {
          const response = await authService.fetchUser();
          const data = unwrapApiObject(response);
          set({
            user: data.user || data,
            isAuthenticated: true,
          });
          return data;
        } catch (error) {
          console.error('Failed to refresh user:', error);
          tokenManager.clearAccessToken();
          set({ user: null, isAuthenticated: false });
          return null;
        }
      }
    }),
    {
      name: 'auth-storage',
      version: 1,
      storage: createJSONStorage(() => localStorage),
      migrate: (persistedState) => {
        return {
          ...persistedState,
          state: {
            ...persistedState?.state,
            access_token: null,
            refresh_token: null,
            isAuthenticated: false,
          },
        };
      },
      onRehydrateStorage: () => (state) => {
        state.setHasHydrated(true);
        if (state.user) state.refreshUser();
      },
      partialize: (state) => ({
        user: state.user,
        isAuthenticated: false,
      }),
    }
  )
);

export default useAuthStore;
