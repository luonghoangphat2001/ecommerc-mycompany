import { useQuery } from '@tanstack/react-query';
import productApi from '../services/productApi';

export const useProducts = (params = {}) => {
  return useQuery({
    queryKey: ['products', params],
    queryFn: () => productApi.getAllProducts(params),
    staleTime: 5 * 60 * 1000,
  });
};

export const useProduct = (id) => {
  return useQuery({
    queryKey: ['product', id],
    queryFn: () => productApi.getProductById(id),
    enabled: !!id,
    staleTime: 5 * 60 * 1000,
  });
};
