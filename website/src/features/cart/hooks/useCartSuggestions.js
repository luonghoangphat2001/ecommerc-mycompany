import { useQuery } from '@tanstack/react-query';
import cartApi from '../services/cartApi';

export const useCartSuggestions = () => {
  return useQuery({
    queryKey: ['cart-suggestions'],
    queryFn: () => cartApi.getCartSuggestions(),
    staleTime: 5 * 60 * 1000,
  });
};
