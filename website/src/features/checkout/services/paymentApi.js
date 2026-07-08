import apiService from '../../../api/apiService';

const paymentApi = {
  getPaymentMethods: () => {
    return apiService.get('checkout/payment-methods');
  },

  processPayment: (orderId, paymentData) => {
    return apiService.post(`checkout/orders/${orderId}/payment`, paymentData);
  },

  processMomoPayment: (orderId) => {
    return apiService.post(`checkout/orders/${orderId}/payment/momo`);
  },

  processVNPayPayment: (orderId) => {
    return apiService.post(`checkout/orders/${orderId}/payment/vnpay`);
  },

  processCODPayment: (orderId) => {
    return apiService.post(`checkout/orders/${orderId}/payment/cod`);
  },

  verifyPayment: (paymentId, transactionId) => {
    return apiService.post('checkout/payment/verify', {
      payment_id: paymentId,
      transaction_id: transactionId,
    });
  },

  getPaymentStatus: (orderId) => {
    return apiService.get(`checkout/orders/${orderId}/payment-status`);
  },
};

export default paymentApi;
