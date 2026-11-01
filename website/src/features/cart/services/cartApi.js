import apiService from '../../../api/apiService';

const cartApi = {
  // POST cart with items from localStorage
  getCart: (items = []) => {
    return apiService.post('cart', { items });
  },

  // Sync and validate cart
  syncCart: (data = {}) => {
    const payload = Array.isArray(data) ? { items: data } : data;
    return apiService.post('cart/sync', payload);
  },

  // Add item to cart
  addToCart: (productId, quantity, variantId = null) => {
    return apiService.post('cart/items', {
      product_id: productId,
      variant_id: variantId,
      quantity,
    });
  },

  // Update item quantity
  updateCartItem: (itemId, quantity) => {
    return apiService.put(`cart/items/${itemId}`, { quantity });
  },

  // Remove item
  removeCartItem: (itemId) => {
    return apiService.delete(`cart/items/${itemId}`);
  },

  // Clear cart
  clearCart: () => {
    return apiService.delete('cart');
  },

  // Get shipping methods for current cart
  getShippingMethods: (items = [], country = 'VN', state = null) => {
    const formattedItems = (items || []).map((item) => ({
      product_id: item.product_id || item.id,
      quantity: item.quantity || item.qty || 1,
      variant_id: item.variant_id || null,
    }));
    return apiService.post('cart/shipping-methods', { items: formattedItems, country, state });
  },

  // Get cart suggestions (cross-sell)
  getCartSuggestions: (items = []) => {
    return apiService.post('cart/suggestions', { items });
  },

  // Apply coupon
  applyCoupon: (code, items = [], subtotal = 0) => {
    return apiService.post('coupons/apply', { code, items, subtotal });
  },

  // Remove coupon and resync cart
  removeCoupon: (items = []) => {
    const formattedItems = (items || []).map((item) => ({
      product_id: item.product_id || item.id,
      quantity: item.quantity || item.qty || 1,
      variant_id: item.variant_id || null,
    }));
    return apiService.post('cart/sync', { items: formattedItems, coupon_code: null });
  },
};

export default cartApi;
