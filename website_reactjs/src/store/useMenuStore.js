import { create } from 'zustand';
import axiosClient from '../api/axiosClient';

const useMenuStore = create((set, get) => ({
  menus: [],
  loading: false,
  error: null,

  fetchMenus: async () => {
    set({ loading: true, error: null });
    try {
      const response = await axiosClient.get('menus');
      set({
        menus: response.data || [],
        loading: false
      });
    } catch (error) {
      console.error('Failed to fetch menus:', error);
      set({ error: error.message, loading: false });
    }
  },

  getMenuBySlug: (slug) => {
    const { menus } = get();
    return menus.find(menu => menu.slug === slug);
  }
}));

export default useMenuStore;
