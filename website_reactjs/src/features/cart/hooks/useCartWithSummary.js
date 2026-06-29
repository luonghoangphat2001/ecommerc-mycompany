import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import cartApi from '../services/cartApi';
import useCartStore from '../store/useCartStore';
import { useEffect } from 'react';

/**
 * Hook to sync cart with backend and get full summary (subtotal, shipping, tax, total)
 */
export const useCartWithSummary = () => {
  const queryClient = useQueryClient();
  const { items, setCart: setLocalCart, summary: localSummary } = useCartStore();

  // Get cart with summary from API
  const { data, isLoading, error } = useQuery({
    queryKey: ['cart', 'summary'],
    queryFn: async () => {
      const localItems = items.map(item => ({
        id: item.id,
        product_id: item.id,
        quantity: item.quantity,
        price: item.price,
      }));
      
      const response = await cartApi.getCart(localItems);
      return response.data;
    },
    enabled: items.length > 0,
    staleTime: 1000 * 60 * 5, // 5 minutes
  });

  // Update local store with server response
  useEffect(() => {
    if (data?.items) {
      // Merge server items with local (for images, names)
      const mergedItems = data.items.map(serverItem => {
        const localItem = items.find(i => i.id === serverItem.id);
        return {
          ...serverItem,
          image: localItem?.image || serverItem.image,
          stock: localItem?.stock || serverItem.stock,
        };
      });
      
      setLocalCart({
        items: mergedItems,
        summary: data.summary,
        notifications: data.notifications,
      });
    }
  }, [data]);

  // Sync cart mutation
  const syncMutation = useMutation({
    mutationFn: (items) => cartApi.syncCart(items),
    onSuccess: (response) => {
      queryClient.setQueryData(['cart', 'summary'], response.data);
    },
  });

  // Get shipping methods
  const getShippingMethods = async (country = 'VN', state = null) => {
    const localItems = items.map(item => ({
      id: item.id,
      product_id: item.id,
      quantity: item.quantity,
      price: item.price,
    }));
    
    const response = await cartApi.getShippingMethods(localItems, country, state);
    return response.data;
  };

  return {
    items: data?.items || items,
    summary: data?.summary || localSummary,
    notifications: data?.notifications || [],
    isLoading,
    error,
    syncCart: syncMutation.mutate,
    getShippingMethods,
    refresh: () => queryClient.invalidateQueries(['cart', 'summary']),
  };
};

export default useCartWithSummary;
