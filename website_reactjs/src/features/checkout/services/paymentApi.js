import axiosClient from '../../../api/axiosClient';

const paymentApi = {
  getPaymentMethods: () => {
    return axiosClient.get('checkout/payment-methods');
  },

  processPayment: (orderId, paymentData) => {
    return axiosClient.post(`checkout/orders/${orderId}/payment`, paymentData);
  },

  processMomoPayment: (orderId) => {
    return axiosClient.post(`checkout/orders/${orderId}/payment/momo`);
  },

  processVNPayPayment: (orderId) => {
    return axiosClient.post(`checkout/orders/${orderId}/payment/vnpay`);
  },

  processCODPayment: (orderId) => {
    return axiosClient.post(`checkout/orders/${orderId}/payment/cod`);
  },

  verifyPayment: (paymentId, transactionId) => {
    return axiosClient.post('checkout/payment/verify', {
      payment_id: paymentId,
      transaction_id: transactionId,
    });
  },

  getPaymentStatus: (orderId) => {
    return axiosClient.get(`checkout/orders/${orderId}/payment-status`);
  },
};

export default paymentApi;
