import axiosClient from '../../../api/axiosClient';

const checkoutApi = {
  createOrder: (orderData) => {
    return axiosClient.post('checkout/orders', orderData);
  },

  validateShippingAddress: (addressData) => {
    return axiosClient.post('checkout/validate-shipping', addressData);
  },

  getShippingMethods: () => {
    return axiosClient.get('checkout/shipping-methods');
  },

  calculateShipping: (addressId, shippingMethodId) => {
    return axiosClient.post('checkout/calculate-shipping', {
      address_id: addressId,
      shipping_method_id: shippingMethodId,
    });
  },

  getOrderSummary: (orderId) => {
    return axiosClient.get(`checkout/orders/${orderId}/summary`);
  },
};

export default checkoutApi;
