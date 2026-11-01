import { useQuery } from '@tanstack/react-query';
import productApi from '../services/productApi';
import useSettingsStore from '../../../store/useSettingsStore';
import { unwrapApiList } from '../../../api/apiResponse';

export const useProducts = (params = {}) => {
  const language = useSettingsStore((state) => state.language);
  const cleanedParams = Object.fromEntries(
    Object.entries(params).filter(([_, v]) => v !== '' && v !== null && v !== undefined)
  );
  return useQuery({
    queryKey: ['products', cleanedParams, language],
    queryFn: () => productApi.getAllProducts(cleanedParams),
    staleTime: 5 * 60 * 1000,
  });
};

export const useProduct = (id) => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['product', id, language],
    queryFn: () => productApi.getProductById(id),
    enabled: !!id,
    staleTime: 5 * 60 * 1000,
  });
};

export const useProductCategories = () => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['product-categories', language],
    queryFn: async () => {
      const response = await productApi.getCategories();
      return unwrapApiList(response, []);
    },
    staleTime: 10 * 60 * 1000,
  });
};

export const useBrands = () => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['brands', language],
    queryFn: async () => {
      const response = await productApi.getBrands();
      return unwrapApiList(response, []);
    },
    staleTime: 10 * 60 * 1000,
  });
};
