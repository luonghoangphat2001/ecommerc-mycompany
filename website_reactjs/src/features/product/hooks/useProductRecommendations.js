import { useQuery } from '@tanstack/react-query';
import productApi from '../services/productApi';

export const useProductRecommendations = (productId) => {
  return useQuery({
    queryKey: ['product-recommendations', productId],
    queryFn: () => productApi.getRecommendations(productId),
    enabled: !!productId,
    staleTime: 5 * 60 * 1000,
  });
};

export const useUpsellProducts = (productId) => {
  return useQuery({
    queryKey: ['product-upsell', productId],
    queryFn: () => productApi.getUpsellProducts(productId),
    enabled: !!productId,
    staleTime: 5 * 60 * 1000,
  });
};

export const useCrossSellProducts = (productId) => {
  return useQuery({
    queryKey: ['product-cross-sell', productId],
    queryFn: () => productApi.getCrossSellProducts(productId),
    enabled: !!productId,
    staleTime: 5 * 60 * 1000,
  });
};

export const useRelatedProducts = (productId) => {
  return useQuery({
    queryKey: ['product-related', productId],
    queryFn: () => productApi.getRelatedProducts(productId),
    enabled: !!productId,
    staleTime: 5 * 60 * 1000,
  });
};
