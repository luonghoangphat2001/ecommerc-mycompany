/**
 * Inventory utilities for multi-warehouse stock management
 */

/**
 * Calculate total stock across all warehouses
 * @param {Array} inventoryArray - Array of inventory objects from API
 * @returns {number} Total stock quantity
 */
export const calculateTotalStock = (inventoryArray) => {
  if (!Array.isArray(inventoryArray) || inventoryArray.length === 0) return 0;
  return inventoryArray.reduce((total, item) => total + (item.quantity || 0), 0);
};

/**
 * Get stock from nearest warehouse based on distance/priority
 * @param {Array} inventoryArray - Array of inventory objects
 * @param {string} userLocation - User's location (optional)
 * @returns {object|null} Nearest warehouse inventory
 */
export const getNearestWarehouseStock = (inventoryArray, userLocation = null) => {
  if (!Array.isArray(inventoryArray) || inventoryArray.length === 0) return null;
  
  // If user location provided, sort by distance (assuming API returns distance field)
  if (userLocation) {
    const sorted = [...inventoryArray].sort((a, b) => (a.distance || 0) - (b.distance || 0));
    return sorted.find(item => item.quantity > 0) || sorted[0];
  }
  
  // Otherwise return warehouse with highest priority or first available
  const withStock = inventoryArray.filter(item => item.quantity > 0);
  if (withStock.length > 0) {
    return withStock.sort((a, b) => (b.priority || 0) - (a.priority || 0))[0];
  }
  
  return inventoryArray[0];
};

/**
 * Check if product is in stock across any warehouse
 * @param {Array} inventoryArray - Array of inventory objects
 * @returns {boolean} True if any warehouse has stock
 */
export const isInStock = (inventoryArray) => {
  if (!Array.isArray(inventoryArray) || inventoryArray.length === 0) return false;
  return inventoryArray.some(item => item.quantity > 0);
};

/**
 * Get inventory status for display
 * @param {Array} inventoryArray - Array of inventory objects
 * @returns {object} { status, message, totalStock }
 */
export const getInventoryStatus = (inventoryArray) => {
  const totalStock = calculateTotalStock(inventoryArray);
  
  if (totalStock === 0) {
    return { status: 'out_of_stock', message: 'Hết hàng', totalStock: 0 };
  }
  
  if (totalStock < 10) {
    return { status: 'low_stock', message: `Còn ${totalStock} sản phẩm`, totalStock };
  }
  
  return { status: 'in_stock', message: 'Còn hàng', totalStock };
};

/**
 * Check if specific variant is available
 * @param {Array} inventoryArray - Array of inventory objects
 * @param {string} variantId - Variant ID to check
 * @returns {boolean} True if variant has stock
 */
export const isVariantAvailable = (inventoryArray, variantId) => {
  if (!Array.isArray(inventoryArray) || !variantId) return false;
  const variantStock = inventoryArray.find(item => item.variant_id === variantId);
  return variantStock ? variantStock.quantity > 0 : false;
};

/**
 * Get stock by warehouse ID
 * @param {Array} inventoryArray - Array of inventory objects
 * @param {string} warehouseId - Warehouse ID
 * @returns {number} Stock quantity
 */
export const getStockByWarehouse = (inventoryArray, warehouseId) => {
  if (!Array.isArray(inventoryArray) || !warehouseId) return 0;
  const warehouse = inventoryArray.find(item => item.warehouse_id === warehouseId);
  return warehouse ? warehouse.quantity : 0;
};
