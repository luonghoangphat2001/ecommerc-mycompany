import axiosClient from '../../../api/axiosClient';

const cartApi = {
  // POST cart with items from localStorage
  getCart: (items = []) => {
    return axiosClient.post('cart', { items });
  },

  // Sync and validate cart
  syncCart: (items = []) => {
    return axiosClient.post('cart/sync', { items });
  },

  // Add item to cart
  addToCart: (productId, quantity, variantId = null) => {
    return axiosClient.post('cart/items', {
      product_id: productId,
      variant_id: variantId,
      quantity,
    });
  },

  // Update item quantity
  updateCartItem: (itemId, quantity) => {
    return axiosClient.put(`cart/items/${itemId}`, { quantity });
  },

  // Remove item
  removeCartItem: (itemId) => {
    return axiosClient.delete(`cart/items/${itemId}`);
  },

  // Clear cart
  clearCart: () => {
    return axiosClient.delete('cart');
  },

  // Get shipping methods for current cart
  getShippingMethods: (items = [], country = 'VN', state = null) => {
    return axiosClient.post('cart/shipping-methods', { items, country, state });
  },

  // Get cart suggestions (cross-sell)
  getCartSuggestions: (items = []) => {
    return axiosClient.post('cart/suggestions', { items });
  },

  // Apply coupon
  applyCoupon: (code, items = [], subtotal = 0) => {
    return axiosClient.post('coupons/apply', { code, items, subtotal });
  },
};

export default cartApi;
