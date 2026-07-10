import { useQuery } from '@tanstack/react-query';
import menuApi from '../services/menuApi';
import { unwrapApiList, unwrapApiObject } from '../../../api/apiResponse';

/**
 * Hook to fetch menu data from API
 */
export const useMenu = (slug = 'main-menu') => {
  const { data, isLoading, error } = useQuery({
    queryKey: ['menu', slug],
    queryFn: async () => {
      try {
        const response = await menuApi.getMenuBySlug(slug);
        return unwrapApiObject(response);
      } catch (error) {
        // Silently fallback if menu not found
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
        return unwrapApiList(response, []);
      } catch (error) {
        // Silently fallback
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
