import apiService from '../../../api/apiService';

const cartApi = {
  // POST cart with items from localStorage
  getCart: (items = []) => {
    return apiService.post('cart', { items });
  },

  // Sync and validate cart
  syncCart: (data = {}) => {
    return apiService.post('cart/sync', data);
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
    return apiService.post('cart/shipping-methods', { items, country, state });
  },

  // Get cart suggestions (cross-sell)
  getCartSuggestions: (items = []) => {
    return apiService.post('cart/suggestions', { items });
  },

  // Apply coupon
  applyCoupon: (code, items = [], subtotal = 0) => {
    return apiService.post('coupons/apply', { code, items, subtotal });
  },
};

export default cartApi;
