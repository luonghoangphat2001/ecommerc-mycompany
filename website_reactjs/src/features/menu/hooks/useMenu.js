import { useQuery } from '@tanstack/react-query';
import menuApi from '../services/menuApi';

/**
 * Hook to fetch menu data from API
 */
export const useMenu = (slug = 'main-menu') => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['menu', slug],
    queryFn: async () => {
      try {
        const response = await menuApi.getMenuBySlug(slug);
        return response.data;
      } catch (error) {
        console.error('Failed to fetch menu:', error);
        return { menu_items: [] };
      }
    },
    staleTime: 1000 * 60 * 30, // 30 minutes
    retry: false,
  });

  return {
    menu: data,
    menuItems: data?.menu_items || [],
    isLoading,
    error,
  };
};

/**
 * Hook to fetch all menus
 */
export const useAllMenus = () => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['menus'],
    queryFn: async () => {
      try {
        const response = await menuApi.getAllMenus();
        return response.data;
      } catch (error) {
        console.error('Failed to fetch menus:', error);
        return [];
      }
    },
    staleTime: 1000 * 60 * 30,
    retry: false,
  });

  return {
    menus: data || [],
    isLoading,
    error,
  };
};

export default useMenu;
