import { useQuery } from '@tanstack/react-query';
import productApi from '../services/productApi';
import { getInventoryStatus, getNearestWarehouseStock } from '../../inventory/utils/inventory';

export const useProductInventory = (productId, variantId = null) => {
  return useQuery({
    queryKey: ['product-inventory', productId, variantId],
    queryFn: () => productApi.getInventory(productId, variantId),
    enabled: !!productId,
    staleTime: 30 * 1000,
    select: (data) => {
      const inventoryArray = data?.data || [];
      return {
        inventory: inventoryArray,
        status: getInventoryStatus(inventoryArray),
        nearestWarehouse: getNearestWarehouseStock(inventoryArray),
      };
    },
  });
};
