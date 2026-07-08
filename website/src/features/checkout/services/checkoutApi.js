import apiService from '../../../api/apiService';

const checkoutApi = {
  createOrder: (orderData) => {
    return apiService.post('checkout/orders', orderData);
  },

  validateShippingAddress: (addressData) => {
    return apiService.post('checkout/validate-shipping', addressData);
  },

  getShippingMethods: () => {
    return apiService.get('checkout/shipping-methods');
  },

  calculateShipping: (addressId, shippingMethodId) => {
    return apiService.post('checkout/calculate-shipping', {
      address_id: addressId,
      shipping_method_id: shippingMethodId,
    });
  },

  getOrderSummary: (orderId) => {
    return apiService.get(`checkout/orders/${orderId}/summary`);
  },
};

export default checkoutApi;
